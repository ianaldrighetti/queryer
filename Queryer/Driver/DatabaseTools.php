<?php
namespace Queryer\Driver;

use Queryer\Exception\DatabaseException;
use Queryer\Database;

/**
 * Class DatabaseTools
 * @package Database\Driver
 */
class DatabaseTools
{
    /**
     * An instance of the Database being used.
     * @var Database|null
     */
    private static $db = null;

    /**
     * Replaces all the variables in the specified query with their assigned value (after being sanitized, of course).
     *
     * @param string $query The query to parse the variables from.
     * @param array $variables An array containing the variables to be replaced in the query.
     * @return string
     * @throws \Queryer\Exception\DatabaseException
     */
    public static function replaceVariables($query, array $variables)
    {
        if (!is_array($variables) || count($variables) == 0)
        {
            return $query;
        }

        // Look for any matches.
        preg_match_all('~{[\w_]+:[\w_]+}~', $query, $matches);

        if (count($matches[0]) > 0)
        {
            $replaceMap = self::getReplaceMap($matches[0], $variables);

            // Now replace them.
            $query = strtr($query, $replaceMap);
        }

        return $query;
    }

    /**
     * Returns an array to use for replacing all variables in a query.
     *
     * @param array $matches
     * @param array $variables
     * @return array
     * @throws \Queryer\Exception\DatabaseException
     */
    private static function getReplaceMap($matches, $variables)
    {
        $replacements = array();
        foreach ($matches as $match)
        {
            // Get the type and variable name.
            list($type, $variable) = explode(':', substr($match, 1, -1));

            $replacements[$match] = self::getReplacement($type, $variable, $variables);
        }

        return $replacements;
    }

    /**
     * Returns a string containing the replacement for the specified variable.
     *
     * @param string $type
     * @param string $variable
     * @param array $variables
     * @throws \Queryer\Exception\DatabaseException
     * @return string
     */
    private static function getReplacement($type, $variable, $variables)
    {
        // First we must check to see if the variable exists.
        if (!array_key_exists($variable, $variables))
        {
            throw new DatabaseException(
                sprintf('The variable %s was not defined.', $variable),
                DatabaseException::UNDEFINED_VARIABLE
            );
        }

        $typeMap = self::getTypeHandlerMap();

        // Does the type exist?
        if (empty($typeMap[$type]))
        {
            throw new DatabaseException(
                sprintf('Unknown data type: %s.', $type),
                DatabaseException::UNKNOWN_DATATYPE
            );
        }

        // Always return NULL for null.
        if (is_null($variables[$variable]))
        {
            return 'NULL';
        }

        // Invoke the proper handler and return.
        return self::$typeMap[$type]($variable, $variables[$variable]);
    }

    /**
     * Returns an array containing the map for which methods process which data types.
     *
     * @return array
     */
    private static function getTypeHandlerMap()
    {
        return array(
            'int' => 'processInteger',
            'double' => 'processDouble',
            'string' => 'processString',
            'raw' => 'processRaw',
            'array_int' => 'processIntegerArray',
            'array_double' => 'processDoubleArray',
            'array_string' => 'processStringArray'
        );
    }

    /**
     * Sanitizes an integer.
     *
     * @param string $variable
     * @param mixed $value
     * @return int
     * @throws \Queryer\Exception\DatabaseException
     */
    private static function processInteger($variable, $value)
    {
        // Make sure it is an integer.
        if ((string)$value !== (string)(int)$value)
        {
            self::throwInvalidTypeException($variable, 'integer', $value);
        }

        return (int)$value;
    }

    /**
     * Sanitizes a double.
     *
     * @param string $variable
     * @param mixed $value
     * @return double
     * @throws \Queryer\Exception\DatabaseException
     */
    private static function processDouble($variable, $value)
    {
        // Make sure it is an double.
        if ((string)$value !== (string)(double)$value)
        {
            self::throwInvalidTypeException($variable, 'double', $value);
        }

        return (double)$value;
    }

    /**
     * Sanitizes a string.
     *
     * @param string $variable
     * @param mixed $value
     * @return string
     * @throws \Queryer\Exception\DatabaseException
     */
    private static function processString($variable, $value)
    {
        // For this one, we need to get the Database.
        $db = self::getDatabaseInstance();

        return '\''. $db->sanitize(htmlspecialchars($value, ENT_QUOTES, 'UTF-8')). '\'';
    }

    /**
     * Returns a value as-is.
     *
     * @param string $variable
     * @param mixed $value
     * @return string
     */
    private static function processRaw($variable, $value)
    {
        return $value;
    }

    /**
     * Sanitizes an array containing integers.
     *
     * @param string $variable
     * @param mixed $value
     * @return string
     * @throws \Queryer\Exception\DatabaseException
     */
    private static function processIntegerArray($variable, $value)
    {
        if (!is_array($value))
        {
            self::throwInvalidTypeException($variable, 'array of integers', $value);
        }

        $collection = array();
        foreach ($value as $str)
        {
            $collection[] = self::processInteger($variable, $str);
        }

        return implode(', ', $collection);
    }

    /**
     * Sanitizes an array containing doubles.
     *
     * @param string $variable
     * @param mixed $value
     * @return string
     * @throws \Queryer\Exception\DatabaseException
     */
    private static function processDoubleArray($variable, $value)
    {
        if (!is_array($value))
        {
            self::throwInvalidTypeException($variable, 'array of doubles', $value);
        }

        $collection = array();
        foreach ($value as $str)
        {
            $collection[] = self::processDouble($variable, $str);
        }

        return implode(', ', $collection);
    }

    /**
     * Sanitizes a string array.
     *
     * @param string $variable
     * @param mixed $value
     * @return string
     * @throws \Queryer\Exception\DatabaseException
     */
    private static function processStringArray($variable, $value)
    {
        if (!is_array($value))
        {
            self::throwInvalidTypeException($variable, 'array of strings', $value);
        }

        $collection = array();
        foreach ($value as $str)
        {
            $collection[] = self::processString($variable, $str);
        }

        return implode(', ', $collection);
    }

    /**
     * Throws a Database Exception indicating that the type of the variable's value was not what it was expected to be.
     *
     * @param string $variable
     * @param string $expectedType
     * @param mixed $value
     * @throws \Queryer\Exception\DatabaseException
     */
    private static function throwInvalidTypeException($variable, $expectedType, $value = null)
    {
        throw new DatabaseException(
            sprintf(
                'Expected variable %s to be of type %s, got %s.',
                $variable,
                $expectedType,
                gettype($value)
            ),
            DatabaseException::TYPE_MISMATCH
        );
    }

    /**
     * Returns an instance of the Database.
     *
     * @return Database
     */
    public static function getDatabaseInstance()
    {
        if (is_null(self::$db))
        {
            self::$db = Database::getInstance();
        }

        return self::$db;
    }

    /**
     * Sets an instance of the Database. This is used for testing purposes.
     *
     * @param Database $db
     */
    public static function setDatabaseInstance(Database $db)
    {
        self::$db = $db;
    }
}
