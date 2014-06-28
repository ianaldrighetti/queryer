<?php
namespace Queryer\Driver;

/**
 * Class DatabaseDriver
 *
 * Every database driver is to implement this abstract class, which implements functionality such as connecting to the
 * database, executing queries against the database and sanitize values for input into the query.
 *
 * @package Queryer\Driver
 */
abstract class DatabaseDriver
{
    /**
     * This will connect to the database, or throw an exception if an error occurred.
     *
     * @param array $options An array containing all the connection options.
     * @return boolean
     * @throws \Queryer\Exception\DatabaseException
     * @see \Queryer\Database
     */
    abstract public function connect(array $options);

    /**
     * The primary method a driver needs to implement is the execute method, which takes an array of query options.
     * The options in the array varies, but the key type will always be supplied, which will be either SELECT, UPDATE,
     * INSERT, REPLACE or DELETE.
     *
     * @param array $options An array of options that were generated through use of the Query class.
     * @return object It is expected to return an instance of an \Queryer\Driver\DatabaseDriverResult class.
     * @see \Queryer\Query, \Queryer\Driver\DatabaseDriverResult
     */
    abstract public function execute($options);

    /**
     * This method is to sanitize the string appropriately for safe insertion into a query.
     *
     * @param string $str
     * @return string
     */
    abstract public function sanitize($str);

    /**
     * This method is to return a string containing a timestamp in the format of the database's TIMESTAMP data type.
     *
     * @param int $timestamp An integer containing the timestamp (i.e. time()). If 0, use current time().
     * @return string
     */
    abstract public function getTimestamp($timestamp = 0);
}
