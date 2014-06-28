<?php
namespace Queryer;

use Queryer\Exception\DatabaseException;

/**
 * Class Database
 *
 * The Database class is used to connect to the selected database using the specified database layer. It is necessary
 * that the Database class be told what engine (MySQL, etc.) to use and any required connection options (typically
 * dependent on the engines themselves).
 *
 * @package Queryer
 */
class Database
{
    /**
     * An instance of the Database Driver.
     * @var \Queryer\Driver\DatabaseDriver
     */
    private $driver;

    /**
     * The name of the Database engine to use (i.e. Mysql).
     * @var string
     */
    private static $engineName = null;

    /**
     * An array containing any options to pass to the Database engine.
     * @var array
     */
    private static $engineOptions = array();

    /**
     * The instance of the Database class.
     * @var Database|null
     */
    private static $instance = null;

    /**
     * The fully qualified name of a driver class for getDriverClassName to return (for testing purposes).
     * @var string
     * @see getDriverClassName
     */
    private static $driverClassName = null;

    /**
     * Initializes the Database instance by making a connection to the database.
     *
     * @param string $engineName The name of the database driver.
     * @param array $engineOptions An array of options to pass to the database driver.
     * @throws Exception\DatabaseException Thrown if the database engine does not exist.
     * @throws Exception\DatabaseException Thrown if the database engine can not connect to the database.
     */
    public function __construct($engineName, array $engineOptions)
    {
        $driverClassName = self::getDriverClassName($engineName);

        // Check if the class exists (an autoload will be attempted).
        if (!class_exists($driverClassName))
        {
            throw new DatabaseException(
                sprintf(
                    'The database driver %s was not found (attempted to autoload "%s").',
                    htmlspecialchars(ucfirst(strtolower($engineName))),
                    htmlspecialchars($driverClassName)
                ),
                DatabaseException::DRIVER_NOT_FOUND
            );
        }

        $this->driver = new $driverClassName();

        // Now connect.
        $this->driver->connect($engineOptions);
    }

    /**
     * Allows direct access to the Database Driver instance.
     *
     * @return Driver\DatabaseDriver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Escapes the string to be safely inserted into a query based on the Driver's sanitize method.
     *
     * @param string $str The string to sanitize.
     * @return string The sanitized string.
     */
    public function sanitize($str)
    {
        return $this->driver->sanitize($str);
    }

    /**
     * Runs a query against the database. The $options parameter is an array of options in order for the query to be
     * built. It is best to use the Query class to build one and then call execute from there.
     *
     * @param array $options The array of options obtained from a Query builder.
     * @return \Queryer\Driver\DatabaseDriverResult
     * @see \Queryer\Query
     */
    public function execute($options)
    {
        return $this->driver->execute($options);
    }

    /**
     * This method returns a string containing a timestamp in the format of the database's TIMESTAMP data type.
     *
     * @param int $timestamp An integer containing the timestamp (i.e. time()). Defaults to 0, resulting in the current
     *                       time being used.
     * @return string
     */
    public function getTimestamp($timestamp = 0)
    {
        return $this->driver->getTimestamp($timestamp);
    }

    /**
     * Returns an instance of the current database.
     *
     * @return Database
     */
    public static function getInstance()
    {
        if (is_null(self::$instance))
        {
            self::$instance = new self(self::$engineName, self::$engineOptions);
        }

        return self::$instance;
    }

    /**
     * Clears the current instance of the Database object.
     */
    public static function clearInstance()
    {
        self::$instance = null;
    }

    /**
     * Returns the name of the current engine being used.
     *
     * @return string
     * @see setEngineName
     */
    public static function getEngineName()
    {
        return self::$engineName;
    }

    /**
     * Sets the database engine name to be used.
     *
     * @param string $engineName
     * @see getEngineName, getInstance
     */
    public static function setEngineName($engineName)
    {
        self::$engineName = $engineName;
    }

    /**
     * Returns an array containing the currently set database engine options.
     *
     * @return array
     * @see setEngineOptions
     */
    public static function getEngineOptions()
    {
        return self::$engineOptions;
    }

    /**
     * Sets the options to be passed to the database engine.
     *
     * @param array $engineOptions
     * @see setEngineName
     */
    public static function setEngineOptions(array $engineOptions)
    {
        self::$engineOptions = $engineOptions;
    }

    /**
     * Returns the fully qualified class name of where the database engine driver should reside.
     *
     * @param string $engineName
     * @return string
     */
    public static function getDriverClassName($engineName)
    {
        if (!is_null(self::$driverClassName))
        {
            return self::$driverClassName;
        }

        // Make sure the first, and only the first, character is uppercased.
        $engineName = ucfirst(strtolower($engineName));

        // Return the full class name of the driver.
        return '\\Queryer\\Engine\\'. $engineName. '\\'. $engineName. 'Driver';
    }

    /**
     * Sets the fully-qualified name of a driver class implementation for getDriverClassName to return, for testing
     * purposes.
     *
     * @param string $driverClassName
     * @see getDriverClassName
     */
    public static function setDriverClassName($driverClassName)
    {
        self::$driverClassName = $driverClassName;
    }
} 