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
        $this->con = new mysqli(
            $options['host'],
            $options['user'],
            $options['pwd'],
            $options['db_name']
        );

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
     * Executes the specified query against the database and returns a MysqlDriverResult object.
     *
     * @param array $options
     * @return \Queryer\Engine\Mysql\MysqlDriverResult
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
            '. $options['select_expr']. '
        FROM '. $options['from']. (!empty($options['from_alias']) ? ' AS '. $options['from_alias'] : '').
        (!empty($options['joins']) && is_array($options['joins']) ? self::generateSelectJoins($options['joins']) : ''). '
        WHERE '. (!empty($options['where_condition']) ? $options['where_condition'] : ' 1 = 1'). (!empty($options['group_by']) ? '
        GROUP BY '. $options['group_by'] : ''). (!empty($options['group_by']) && !empty($options['having']) ? '
        HAVING '. $options['having'] : ''). (!empty($options['order_by']) ? '
        ORDER BY '. $options['order_by'] : ''). (!empty($options['limit']) ? '
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
            '. strtoupper($join['type']). ' JOIN '. $join['table']. ' AS '. $join['table_alias']. ' ON '. $join['condition'];
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
        UPDATE '. (!empty($options['ignore']) ? 'IGNORE ' : ''). $options['table']. '
        SET '. $options['values']. '
        WHERE '. (!empty($options['where_condition']) ? $options['where_condition'] : '1 = 1'). (!empty($options['order_by']) ? '
        ORDER BY '. $options['order_by'] : ''). (!empty($options['limit']) ? '
        LIMIT '. $options['limit'] : '');
    }

    /**
     * Generates an INSERT or REPLACE query based off the options.
     *
     * @param array $options
     * @return string
     */
    private static function generateInsertOrReplaceQuery($options)
    {
        return '
        '. ($options['type'] == 'INSERT' ? 'INSERT' : 'REPLACE'). ' '. (!empty($options['ignore']) ? 'IGNORE ' : ''). 'INTO '. $options['table']. '
        (`'. implode('`, `', array_keys($options['values'])). '`)
        VALUES('. implode(', ', array_values($options['values'])). ')';
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
        DELETE FROM '. $options['from']. '
        WHERE '. (!empty($options['where_condition']) ? $options['where_condition'] : '1 = 1'). (!empty($options['order_by']) ? '
        ORDER BY '. $options['order_by'] : ''). (!empty($options['limit']) ? '
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
}