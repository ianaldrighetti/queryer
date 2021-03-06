<?php
namespace Queryer\Engine\Mysql;

use mysqli;
use Queryer\Driver\DatabaseDriver;
use Queryer\Driver\DatabaseTools;
use Queryer\Exception\DatabaseException;

/**
 * Class MysqlDriver
 * @package Rsvp\Database\Engine\Mysql
 */
class MysqlDriver extends DatabaseDriver
{
    /**
     * @var mysqli
     */
    private $con;

    /**
     * An instance for mocking out a mysqli instance.
     * @var MysqliInterface
     */
    private static $mysqliInstance = null;

    /**
     * Initializes the MySQL Driver (just sets the connection to null).
     */
    public function __construct()
    {
        $this->con = null;
    }

    /**
     * Establishes a connection to the MySQL server.
     *
     * @param array $options
     * @throws \Queryer\Exception\DatabaseException
     * @return bool
     */
    public function connect(array $options)
    {
        // Make sure all necessary options are there.
        $this->checkConnectionOptions($options);

        $this->con = $this->getMysqliInstance($options);

        if (mysqli_connect_error() !== null)
        {
            throw new DatabaseException(
                mysqli_connect_error(),
                DatabaseException::CONNECTION_ERROR
            );
        }

        return true;
    }

    /**
     * Returns an instance of mysqli or MysqliInterface.
     *
     * @param array $options
     * @return mysqli|MysqliInterface
     */
    private function getMysqliInstance(array $options)
    {
        if (!is_null(self::$mysqliInstance))
        {
            self::$mysqliInstance->setOptions($options);

            return self::$mysqliInstance;
        }

        return new mysqli($options['host'], $options['user'], $options['pwd'], $options['db_name']);
    }

    /**
     * Checks to ensure that all necessary options have been supplied for connecting to the database.
     *
     * @param array &$options
     * @throws \Queryer\Exception\DatabaseException Thrown if there is an option missing.
     */
    private function checkConnectionOptions(array &$options)
    {
        $required = array('host', 'user', 'db_name');

        foreach ($required as $requiredOption)
        {
            // Just check if the key exists
            if (!array_key_exists($requiredOption, $options))
            {
                throw new DatabaseException(
                    sprintf(
                        'The MySQL driver requires the following engine options: %s.',
                        implode(', ', $required)
                    )
                );
            }
        }

        // If the password option is missing, default to no password.
        if (!array_key_exists('pwd', $options))
        {
            $options['pwd'] = '';
        }
    }

    /**
     * Executes the specified query against the database and returns a MysqlDriverResult object.
     *
     * @param array $options
     * @return \Queryer\Engine\Mysql\MysqlDriverResult
     * @throws \Queryer\Exception\DatabaseException Thrown if there are no rows or row keys do not match.
     */
    public function execute(array $options)
    {
        $query = self::generateQuery($options);

        // We need to replace anything that needs, well, replacing.
        $query = DatabaseTools::replaceVariables($query, $options['variables']);

        // Execute the query against the database.
        $result = @$this->con->query($query);

        // Now return the result (if any error occurred it will all be processed there).
        return new MysqlDriverResult(
            $result,
            $this->con->affected_rows,
            $this->con->insert_id,
            $this->con->errno,
            $this->con->error,
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
        (!empty($options['joins']) && is_array($options['joins']) ? self::generateSelectJoins($options['joins']) : ''). '
        WHERE '. (!empty($options['condition']) ? $options['condition'] : ' 1 = 1'). (!empty($options['groupBy']) ? '
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
        '. ($options['type'] == 'INSERT' ? 'INSERT' : 'REPLACE'). ' '.
        (!empty($options['ignore']) && $options['type'] == 'INSERT' ? 'IGNORE ' : ''). 'INTO '. $options['table']. '
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
     * Escapes the string for safe insertion into a query.
     *
     * @param string $str
     * @return string
     */
    public function sanitize($str)
    {
        return $this->con->real_escape_string($str);
    }

    /**
     * Returns a MySQL TIMESTAMP formatted date.
     *
     * @param int $timestamp
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

    /**
     * Sets a mocker for the mysqli instance.
     *
     * @param MysqliInterface $mysqliInstance
     */
    public static function setMysqliInstance(MysqliInterface $mysqliInstance)
    {
        self::$mysqliInstance = $mysqliInstance;
    }

    /**
     * Clears the mysqli mocker instance.
     */
    public static function clearMysqliInstance()
    {
        self::$mysqliInstance = null;
    }
}
