<?php
namespace QueryerTests\Mocker;

use Queryer\Driver\DatabaseDriver;

/**
 * Class MockDatabaseDriver
 *
 * A mock implementation of a database driver for testing purposes.
 *
 * @package QueryerTests\Mocker
 */
class MockDatabaseDriver extends DatabaseDriver
{
    /**
     * @var mixed
     */
    private $connect;

    /**
     * @var mixed
     */
    private $execute;

    /**
     * @var mixed
     */
    private $sanitize;

    /**
     * @var mixed
     */
    private $getTimestamp;

    /**
     * Initializes everything to null.
     */
    public function __construct()
    {
        $this->connect = null;
        $this->execute = null;
        $this->sanitize = null;
        $this->getTimestamp = null;
    }

    /**
     * This will connect to the database, or throw an exception if an error occurred.
     *
     * @param array $options An array containing all the connection options.
     * @return boolean
     * @throws \Queryer\Exception\DatabaseException
     * @see \Queryer\Database
     */
    public function connect(array $options)
    {
        $this->connect = $options;
    }

    /**
     * Returns what connect was invoked with.
     *
     * @return mixed
     */
    public function getConnectInvokedWith()
    {
        return $this->connect;
    }

    /**
     * The primary method a driver needs to implement is the execute method, which takes an array of query options.
     * The options in the array varies, but the key type will always be supplied, which will be either SELECT, UPDATE,
     * INSERT, REPLACE or DELETE.
     *
     * @param array $options An array of options that were generated through use of the Query class.
     * @return object It is expected to return an instance of an \Queryer\Driver\DatabaseDriverResult class.
     * @see \Queryer\Query, \Queryer\Driver\DatabaseDriverResult
     */
    public function execute(array $options)
    {
        $this->execute = $options;

        return null;
    }

    /**
     * Returns what execute was invoked with.
     *
     * @return mixed
     */
    public function getExecuteInvokedWith()
    {
        return $this->execute;
    }

    /**
     * This method is to sanitize the string appropriately for safe insertion into a query.
     *
     * @param string $str
     * @return string
     */
    public function sanitize($str)
    {
        $this->sanitize = $str;

        return addcslashes($str, "'");
    }

    /**
     * Returns what sanitize was invoked with.
     *
     * @return mixed
     */
    public function getSanitizeInvokedWith()
    {
        return $this->sanitize;
    }

    /**
     * This method is to return a string containing a timestamp in the format of the database's TIMESTAMP data type.
     *
     * @param int $timestamp An integer containing the timestamp (i.e. time()). If 0, use current time().
     * @return string
     */
    public function getTimestamp($timestamp = 0)
    {
        $this->getTimestamp = $timestamp;

        return '';
    }

    /**
     * Returns what getTimestamp was invoked with.
     *
     * @return mixed
     */
    public function getGetTimestampInvokedWith()
    {
        return $this->getTimestamp;
    }
}