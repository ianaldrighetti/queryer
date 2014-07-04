<?php
namespace Queryer\Query;

/**
 * Class SelectQuery
 * @package Queryer\Query
 */
class SelectQuery extends QueryType
{
    /**
     * Initializes the SELECT builder.
     */
    public function __construct()
    {
        parent::__construct('SELECT');
    }

    /**
     * Sets the DISTINCT flag for the query.
     *
     * @param bool $isDistinct Whether the query should return distinct results. Defaults to false.
     * @return $this
     */
    public function isDistinct($isDistinct)
    {
        $this->setOption('distinct', !empty($isDistinct));

        return $this;
    }

    /**
     * The SELECT expression for the query (such as a comma separated list of columns to return).
     *
     * @param string $expr The SELECT expression.
     * @return $this
     */
    public function expr($expr)
    {
        $this->setOption('expr', $expr);

        return $this;
    }

    /**
     * Sets the table the data is being retrieved from, along with an optional alias to use when referencing the table.
     *
     * @param string $table The table name.
     * @param string $alias The alias of the table (optional).
     * @return $this
     */
    public function from($table, $alias = null)
    {
        $this->setOption('table', $table);

        if (!is_null($alias))
        {
            $this->setOption('alias', $alias);
        }

        return $this;
    }

    /**
     * Adds a JOIN to the query.
     *
     * @param string $type The type of join, such as INNER, LEFT, RIGHT, etc.
     * @param string $table The table to join.
     * @param string $alias The alias of the table to use when referencing it.
     * @param string $condition The join condition.
     * @return $this
     */
    public function join($type, $table, $alias, $condition)
    {
        // Get the existing JOINs.
        $joins = $this->getOption('joins', array());

        // Add the new one.
        $joins[] = array(
            'type' => $type,
            'table' => $table,
            'alias' => $alias,
            'condition' => $condition,
        );

        $this->setOption('joins', $joins);

        return $this;
    }

    /**
     * The WHERE clause of the SELECT query.
     *
     * @param string $condition The WHERE clause.
     * @return $this
     */
    public function where($condition)
    {
        $this->setOption('condition', $condition);

        return $this;
    }

    /**
     * Adds a GROUP BY clause to the query.
     *
     * @param string $groupBy The GROUP BY clause (e.g. "user_id ASC").
     * @return $this
     * @see having
     */
    public function groupBy($groupBy)
    {
        $this->setOption('groupBy', $groupBy);

        return $this;
    }

    /**
     * The HAVING clause to go along with the GROUP BY clause.
     *
     * @param string $having The HAVING condition.
     * @return $this
     * @see groupBy
     */
    public function having($having)
    {
        $this->setOption('having', $having);

        return $this;
    }

    /**
     * Adds an ORDER BY clause to the query.
     *
     * @param string $orderBy An ORDER BY clause (e.g. "user_id DESC").
     * @return $this
     */
    public function orderBy($orderBy)
    {
        $this->setOption('orderBy', $orderBy);

        return $this;
    }

    /**
     * Sets the maximum number of rows to be returned in the result set.
     *
     * @param int $limit The maximum number of rows to be returned. If null this option will not be set.
     * @return $this
     */
    public function limit($limit)
    {
        if (is_null($limit))
        {
            return $this;
        }

        $this->setOption('limit', $limit);

        return $this;
    }

    /**
     * Sets the offset of where to begin returning rows for the result set (starting at 0).
     *
     * @param int $offset The offset. If null this option will not be set.
     * @return $this
     */
    public function offset($offset)
    {
        if (is_null($offset))
        {
            return $this;
        }

        $this->setOption('offset', $offset);

        return $this;
    }
}
