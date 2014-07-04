<?php
namespace Queryer\Query;

use Queryer\Database;
use Queryer\Mock\QueryMockerInterface;

/**
 * Class QueryType
 *
 * The QueryType class is for use in implementing a Query generator for different Query types.
 *
 * @package Queryer\Query
 */
class QueryType
{
    /**
     * An array of options for the query being built.
     * @var array
     */
    private $options;

    /**
     * A mocker, for testing purposes.
     * @var QueryMockerInterface
     */
    private static $mocker = null;

    /**
     * Initializes the QueryType with a type.
     *
     * @param string $type The type of query, such as SELECT, UPDATE, etc.
     */
    public function __construct($type)
    {
        $this->options = array();
        $this->setOption('type', strtoupper($type));
    }

    /**
     * Sets the option. Setting the type option is forbidden.
     *
     * @param string $option The option name.
     * @param mixed $value The option value.
     */
    protected function setOption($option, $value)
    {
        if ($option == 'type')
        {
            return;
        }

        $this->options[$option] = $value;
    }

    /**
     * Returns the options value.
     *
     * @param string $option The option name.
     * @return mixed The options value, or null if it does not exist.
     */
    protected function getOption($option)
    {
        return array_key_exists($option, $this->options) ? $this->options[$option] : null;
    }

    /**
     * Returns an array containing all the options for the built query.
     *
     * @return array
     */
    public final function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets an array of variables to replace, where the key is the variable name.
     *
     * @param array $variables
     */
    public final function replace(array $variables)
    {
        $this->setOption('variables', $variables);
    }

    /**
     * Executes the built query and returns a Database Result object.
     *
     * @return \Queryer\Driver\DatabaseDriverResult
     */
    public final function execute()
    {
        // Is someone mocking us? Well... that's not very nice!
        if (!is_null(self::$mocker))
        {
            return self::$mocker->execute($this->options);
        }

        // Otherwise we'll hit up the driver.
        return Database::getInstance()->execute($this->options);
    }

    /**
     * Sets a mocker that will be able to watch what's going on, without letting anything actually hit the database.
     *
     * @param QueryMockerInterface $mock
     * @see \Queryer\Mock\QueryMocker
     */
    public static function setMocker(QueryMockerInterface $mock)
    {
        self::$mocker = $mock;
    }

    /**
     * Returns the currently set Query Mocker.
     *
     * @return QueryMockerInterface
     */
    public static function getMocker()
    {
        return self::$mocker;
    }

    /**
     * Clears the current mocker that was set by the setMocker method.
     *
     * @see setMocker
     */
    public static function clearMocker()
    {
        self::$mocker = null;
    }
}