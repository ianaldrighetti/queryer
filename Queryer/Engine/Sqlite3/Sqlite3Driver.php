<?php
namespace Queryer\Engine\Sqlite3;

use Queryer\Driver\DatabaseDriver;
use Queryer\Driver\DatabaseTools;
use Queryer\Exception\DatabaseException;

/**
 * Class Sqlite3Driver
 * @package Queryer\Engine\Sqlite3
 */
class Sqlite3Driver extends DatabaseDriver
{
    /**
     * @var \SQLite3
     */
    private $sqlite;

    /**
     * @var Sqlite3Interface
     */
    private static $sqliteInstance;

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
        $this->checkConnectionOptions($options);

        try
        {
            $this->sqlite = $this->getSqlite3Instance($options);
        }
        catch (\Exception $ex)
        {
            throw new DatabaseException(
                $ex->getMessage(),
                DatabaseException::CONNECTION_ERROR,
                $ex
            );
        }

        return true;
    }

    /**
     * Returns an instance of an SQLite3 object or Sqlite3Interface.
     *
     * @param array $options
     * @return Sqlite3Interface|\SQLite3
     */
    private function getSqlite3Instance(array $options)
    {
        if (!is_null(self::$sqliteInstance))
        {
            return self::$sqliteInstance;
        }

        return empty($options['encryption_key']) ? new \SQLite3($options['filename'], $options['flags'])
            : new \SQLite3($options['filename'], $options['flags'], $options['encryption_key']);
    }

    /**
     * Checks to ensure all required options have been specified.
     *
     * @param array $options
     * @throws \Queryer\Exception\DatabaseException Thrown if there are any missing options.
     */
    private function checkConnectionOptions(array $options)
    {
        // We just require the filename.
        if (!array_key_exists('filename', $options))
        {
            throw new DatabaseException(
                'The SQLite3 driver requires the following engine options: filename.'
            );
        }

        if (!array_key_exists('flags', $options))
        {
            $options['flags'] = SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE;
        }
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
        $query = self::generateQuery($options);

        $query = DatabaseTools::replaceVariables($query, $options['variables']);

        $result = $this->sqlite->query($query);

        return new Sqlite3DriverResult(
            $result,
            $this->sqlite->changes(),
            $this->sqlite->lastInsertRowID(),
            $result === false ? $this->sqlite->lastErrorCode() : null,
            $result === false ? $this->sqlite->lastErrorMsg() : null,
            $query
        );
    }

    /**
     * Generates a query from a set of options.
     *
     * @param array $options
     * @return string
     * @throws \Queryer\Exception\DatabaseException Thrown if there are no rows or row keys do not match.
     */
    public static function generateQuery($options)
    {
        $query = null;

        // Let's see what we got here.
        switch(strtoupper($options['type']))
        {
            case 'SELECT':
                $query = self::generateSelectQuery($options);
                break;

            case 'UPDATE':
                $query = self::generateUpdateQuery($options);
                break;

            case 'INSERT':
                $query = self::generateInsertOrReplaceQuery($options);
                break;

            case 'REPLACE':
                $query = self::generateInsertOrReplaceQuery($options);
                break;

            case 'DELETE':
                $query = self::generateDeleteQuery($options);
                break;
        }

        return $query;
    }

    /**
     * Generates a SELECT query based off the specified options.
     *
     * @param array $options
     * @return string
     */
    private static function generateSelectQuery($options)
    {
        return '
        SELECT'. (!empty($options['distinct']) ? ' DISTINCT ' : ''). '
            '. $options['expr']. '
        FROM '. $options['table']. (!empty($options['alias']) ? ' AS '. $options['alias'] : '').
        (!empty($options['joins']) && is_array($options['joins']) ? self::generateSelectJoins($options['joins']) : '').
        (!empty($options['condition']) ? '
        WHERE '. $options['condition'] : ''). (!empty($options['groupBy']) ? '
        GROUP BY '. $options['groupBy'] : ''). (!empty($options['groupBy']) && !empty($options['having']) ? '
        HAVING '. $options['having'] : ''). (!empty($options['orderBy']) ? '
        ORDER BY '. $options['orderBy'] : ''). (!empty($options['limit']) ? '
        LIMIT '. (!empty($options['offset']) ? $options['offset']. ', ' : ''). $options['limit'] : '');
    }

    /**
     * Generates the JOIN's to add to the SELECT query.
     *
     * @param array $joins
     * @return string
     * @see generateSelectQuery
     */
    private static function generateSelectJoins($joins)
    {
        $join_query = '';
        foreach ($joins as $join)
        {
            $join_query .= '
            '. strtoupper($join['type']). ' JOIN '. $join['table']. ' AS '. $join['alias']. ' ON '. $join['condition'];
        }

        return $join_query;
    }

    /**
     * Generates an UPDATE query based off the options.
     *
     * @param array $options
     * @return string
     */
    private static function generateUpdateQuery($options)
    {
        return '
        UPDATE '. $options['table']. '
        SET '. self::getUpdateSetValues($options['set']). (!empty($options['condition']) ? '
        WHERE '. $options['condition'] : ''). (!empty($options['limit']) ? '
        LIMIT '. $options['limit'] : '');
    }

    /**
     * Generates the SET clause for an UPDATE query.
     *
     * @param array $values An array with the key being the column name and the value being the value to set the column
     *                      to.
     * @return string
     */
    private static function getUpdateSetValues(array $values)
    {
        $columns = array();
        foreach ($values as $column => $value)
        {
            $columns[] = $column. ' = '. (is_null($value) ? 'NULL' : $value);
        }

        return implode(', ', $columns);
    }

    /**
     * Generates an INSERT or REPLACE query based off the options.
     *
     * @param array $options
     * @return string
     * @throws \Queryer\Exception\DatabaseException Thrown if there are no rows or row keys do not match.
     */
    private static function generateInsertOrReplaceQuery($options)
    {
        return '
        INSERT '. ($options['type'] == 'REPLACE' ? 'OR REPLACE' : (!empty($options['ignore']) ? 'OR IGNORE' : '')).
        'INTO '. $options['table']. '
        (`'. implode('`, `', self::getInsertColumnNames($options['rows'])). '`)
        VALUES'. implode(',', self::getInsertColumnValues($options['rows']));
    }

    /**
     * Generates the column names array for an INSERT or REPLACE query.
     *
     * @param array $rows
     * @return array
     * @throws \Queryer\Exception\DatabaseException Thrown if there are no rows or row keys do not match.
     */
    private static function getInsertColumnNames(array $rows)
    {
        $rowCount = count($rows);

        // We can't have 0 rows...
        if ($rowCount == 0)
        {
            throw new DatabaseException(
                'There must be at least one row specified to insert or replace.',
                DatabaseException::INVALID_QUERY
            );
        }

        // We will go with the first row (since there must be at least one) to use to match against all others.
        $columns = array_keys($rows[0]);

        $mismatch = array();
        for ($i = 1; $i < $rowCount; $i++)
        {
            // If keys don't match, add it to the mismatches.
            if ($columns != array_keys($rows[$i]))
            {
                $mismatch[] = $i + 1;
            }
        }

        if (count($mismatch) > 0)
        {
            throw new DatabaseException(
                sprintf(
                    'Row keys do not match, found at row values %s.',
                    self::getInsertDoNotMatchStatement($mismatch)
                ),
                DatabaseException::INVALID_QUERY
            );
        }

        return $columns;
    }

    /**
     * Returns a statement such as "1, 2 and 4."
     *
     * @param array $mismatches
     * @return string
     */
    private static function getInsertDoNotMatchStatement(array $mismatches)
    {
        if (count($mismatches) == 1)
        {
            return $mismatches[0];
        }

        $chunks = array_chunk($mismatches, count($mismatches) - 1);

        // Make it nice and pretty.
        return implode(', ', $chunks[0]). ' and '. $chunks[1][0];
    }

    /**
     * Generates the rows for the VALUES component of the INSERT or REPLACE query.
     *
     * @param array $rows
     * @return array
     */
    private static function getInsertColumnValues(array $rows)
    {
        $data = array();
        foreach ($rows as $row)
        {
            $data[] = '('. implode(', ', array_values($row)). ')';
        }

        return $data;
    }

    /**
     * Generates a DELETE query based off the options.
     *
     * @param array $options
     * @return string
     */
    private static function generateDeleteQuery($options)
    {
        return '
        DELETE FROM '. $options['table']. (!empty($options['condition']) ? '
        WHERE '. $options['condition'] : ''). (!empty($options['orderBy']) ? '
        ORDER BY '. $options['orderBy'] : ''). (!empty($options['limit']) ? '
        LIMIT '. $options['limit'] : '');
    }

    /**
     * This method is to sanitize the string appropriately for safe insertion into a query.
     *
     * @param string $str
     * @return string
     */
    public function sanitize($str)
    {
        return $this->sqlite->escapeString($str);
    }

    /**
     * This method is to return a string containing a timestamp in the format of the database's TIMESTAMP data type.
     *
     * @param int $timestamp An integer containing the timestamp (i.e. time()). If 0, use current time().
     * @return string
     */
    public function getTimestamp($timestamp = 0)
    {
        if ($timestamp === 0)
        {
            $timestamp = time();
        }

        return date('Y-m-d H:i:s', $timestamp);
    }
}
 