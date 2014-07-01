<?php
namespace Queryer\Exception;

/**
 * Class DatabaseException
 *
 * This is an Exception class that is thrown if anything goes wrong while attempting to connect to the database or if
 * an error occurs while attempting to generate a query from the Query builder instance.
 *
 * @package Queryer\Exception
 * @see \Queryer\Query
 */
class DatabaseException extends \Exception
{
    /**
     * Indicates that a variable referenced in a query was not defined.
     */
    const UNDEFINED_VARIABLE = 1;

    /**
     * Indicates that the type specified for a variable is unknown.
     */
    const UNKNOWN_DATATYPE = 2;

    /**
     * Indicates that the the variable's value was not of the type defined.
     */
    const TYPE_MISMATCH = 3;

    /**
     * Indicates that an error occurred while setting up the connection to the database.
     */
    const CONNECTION_ERROR = 4;

    /**
     * Indicates that the specified database driver was not found.
     */
    const DRIVER_NOT_FOUND = 5;

    /**
     * Indicates that no database engine was ever specified.
     */
    const ENGINE_NOT_SPECIFIED = 6;
}
