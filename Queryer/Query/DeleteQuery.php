<?php
namespace Queryer\Query;
use Queryer\Query;

/**
 * Class DeleteQuery
 * @package Queryer\Query
 */
class DeleteQuery extends QueryType
{
    /**
     * Creates a DELETE query builder.
     */
    public function __construct()
    {
        parent::__construct('DELETE');
    }

    /**
     * Designates the table that will be deleted from.
     *
     * @param string $table The table name.
     * @return $this
     */
    public function from($table)
    {
        $this->setOption('table', $table);

        return $this;
    }

    /**
     * Sets the WHERE clause of the delete query.
     *
     * @param string $condition The where condition.
     * @return $this
     */
    public function where($condition)
    {
        $this->setOption('condition', $condition);

        return $this;
    }

    /**
     * Sets the ORDER BY of the delete query.
     *
     * @param string $orderBy The columns to order by (e.g. "user_id ASC").
     * @return $this
     */
    public function orderBy($orderBy)
    {
        $this->setOption('orderBy', $orderBy);

        return $this;
    }

    /**
     * Sets the limit on how many rows should be deleted, maximum.
     *
     * @param int $limit The maximum number of rows to delete. If $limit is null it will not be set.
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
}
