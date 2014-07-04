<?php
namespace Queryer;

use InvalidArgumentException;
use Queryer\Mock\QueryMockerInterface;
use Queryer\Query\DeleteQuery;
use Queryer\Query\InsertQuery;
use Queryer\Query\QueryType;
use Queryer\Query\ReplaceQuery;
use Queryer\Query\SelectQuery;
use Queryer\Query\UpdateQuery;

/**
 * Class Query
 *
 * The Query class offers a Query building interface to the database.
 *
 * @package Queryer
 */
class Query
{
    /**
     * The array containing all of the query options that have been added.
     *
     * @var array
     */
    private $options;

    /**
     * An instance of a Query Mocker for testing.
     * @var QueryMockerInterface
     * @see \Queryer\Mock\QueryMocker
     */
    private static $mocker = null;

    /**
     * Initializes a query builder of the specified type.
     *
     * @param string $type The type of query being built.
     * @throws \InvalidArgumentException
     */
    public function __construct($type)
    {
        $type = strtoupper($type);

        // Make sure this is a type we know.
        if (!in_array($type, array('SELECT', 'UPDATE', 'INSERT', 'REPLACE', 'DELETE')))
        {
            throw new InvalidArgumentException(
                sprintf('The query type %s is unknown.', htmlspecialchars($type))
            );
        }

        // Everything should be empty (except for a couple things).
        $this->options = array(
            'type' => $type,
            'variables' => array(),
            'distinct' => null,
            'select_expr' => null,
            'from' => null,
            'from_alias' => null,
            'joins' => array(),
            'where_condition' => null,
            'group_by' => null,
            'having' => null,
            'order_by' => null,
            'limit' => null,
            'offset' => null,
            'ignore' => null,
            'table' => null,
            'values' => null,
            'keys' => null
        );
    }

    /**
     * Adds the variables to the currently set variables (merges them, will overwrite if necessary).
     *
     * @param array $variables
     * @return $this
     */
    public function variables($variables)
    {
        $this->options['variables'] = array_merge($this->options['variables'], $variables);

        return $this;
    }

    /**
     * Sets whether the result set should be distinct.
     *
     * @param bool $distinct
     * @return $this
     */
    public function distinct($distinct)
    {
        $this->options['distinct'] = !empty($distinct);

        return $this;
    }

    /**
     * Sets the SELECT column expression (i.e. SELECT select_expr FROM ...).
     *
     * @param string $select_expr
     * @return $this
     */
    public function selectExpr($select_expr)
    {
        $this->options['select_expr'] = $select_expr;

        return $this;
    }

    /**
     * Sets the table from which to SELECT or DELETE.
     *
     * @param string $from
     * @return $this
     */
    public function from($from)
    {
        $this->options['from'] = $from;

        return $this;
    }

    /**
     * Sets the alias to be used in the SELECT for the table being selected from.
     *
     * @param string $from_alias
     * @return $this
     */
    public function alias($from_alias)
    {
        $this->options['from_alias'] = $from_alias;

        return $this;
    }

    /**
     * Adds a JOIN clause to the SELECT.
     *
     * @param string $type The type of join (LEFT, RIGHT, INNER, etc.).
     * @param string $table The table to join.
     * @param string $alias The alias for the table being joined.
     * @param string $condition The condition on which to join it.
     * @return $this
     */
    public function join($type, $table, $alias, $condition)
    {
        $this->options['joins'][] = array(
            'type' => $type,
            'table' => $table,
            'table_alias' => $alias,
            'condition' => $condition
        );

        return $this;
    }

    /**
     * Sets the WHERE clause for a SELECT, UPDATE or DELETE query.
     *
     * @param string $where
     * @return $this
     */
    public function where($where)
    {
        $this->options['where_condition'] = $where;

        return $this;
    }

    /**
     * Sets the GROUP BY clause for a SELECT query.
     *
     * @param string $group_by
     * @return $this
     */
    public function groupBy($group_by)
    {
        $this->options['group_by'] = $group_by;

        return $this;
    }

    /**
     * Sets the HAVING clause for a SELECT query.
     *
     * @param string $having
     * @return $this
     */
    public function having($having)
    {
        $this->options['having'] = $having;

        return $this;
    }

    /**
     * Sets the ORDER BY clause for a SELECT, UPDATE or DELETE query.
     *
     * @param string $order_by
     * @return $this
     */
    public function orderBy($order_by)
    {
        $this->options['order_by'] = $order_by;

        return $this;
    }

    /**
     * Sets the LIMIT clause for a SELECT, UPDATE or DELETE query.
     *
     * @param int $limit If null, then it is not set.
     * @return $this
     */
    public function limit($limit)
    {
        if (is_null($limit))
        {
            return $this;
        }

        $this->options['limit'] = (int)$limit;

        return $this;
    }

    /**
     * Sets the offset component of a LIMIT query for a SELECT.
     *
     * @param int $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->options['offset'] = (int)$offset;

        return $this;
    }

    /**
     * Sets whether issues (such as duplicate key errors) should be ignored in UPDATE and INSERT queries.
     *
     * @param bool $ignore
     * @return $this
     */
    public function ignore($ignore)
    {
        $this->options['ignore'] = !empty($ignore);

        return $this;
    }

    /**
     * Sets the table being updated, inserted into or having rows replaced.
     *
     * @param string $table
     * @return $this
     */
    public function table($table)
    {
        $this->options['table'] = $table;

        return $this;
    }

    /**
     * Sets the values to be inserted, replaced or updated in a table (column name => value pair).
     *
     * @param array $values
     * @return $this
     */
    public function values($values)
    {
        $this->options['values'] = $values;

        return $this;
    }

    /**
     * Sets an array indicating the column names that are the primary columns in a table (in order for REPLACE to know
     * which to check for duplicate values).
     *
     * @param array $keys
     * @return $this
     */
    public function keys($keys)
    {
        $this->options['keys'] = $keys;

        return $this;
    }

    /**
     * Returns the array containing the query information added so far.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Executes the built query.
     *
     * @return \Queryer\Driver\DatabaseDriverResult
     */
    public function execute()
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
        QueryType::setMocker($mock);
    }

    /**
     * Returns the currently set Query Mocker.
     *
     * @return QueryMockerInterface
     */
    public static function getMocker()
    {
        return QueryType::getMocker();
    }

    /**
     * Clears the current mocker that was set by the setMocker method.
     *
     * @see setMocker
     */
    public static function clearMocker()
    {
        QueryType::clearMocker();
    }

    /**
     * Creates a new instance of the Query builder.
     *
     * @param string $type The type of query to create.
     * @return Query
     * @deprecated Please use the newer methods for returning query builders made for specific query types.
     * @see delete
     * @see insert
     * @see replace
     * @see select
     * @see update
     */
    public static function create($type)
    {
        return new Query($type);
    }

    /**
     * Creates a new instance of a DELETE query builder.
     *
     * @return DeleteQuery
     */
    public static function delete()
    {
        return new DeleteQuery();
    }

    /**
     * Creates a new instance of an INSERT query builder.
     *
     * @return InsertQuery
     */
    public static function insert()
    {
        return new InsertQuery(false);
    }

    /**
     * Creates a new instance of a REPLACE query builder.
     *
     * @return ReplaceQuery
     */
    public static function replace()
    {
        return new ReplaceQuery();
    }

    /**
     * Creates a new instance of a SELECT query builder.
     *
     * @return SelectQuery
     */
    public static function select()
    {
        return new SelectQuery();
    }

    /**
     * Creates a new instance of an UPDATE query builder.
     *
     * @return UpdateQuery
     */
    public static function update()
    {
        return new UpdateQuery();
    }
}
