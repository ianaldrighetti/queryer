<?php
namespace Queryer\Query;

/**
 * Class UpdateQuery
 * @package Queryer\Query
 */
class UpdateQuery extends QueryType
{
    /**
     * Initializes the UPDATE query builder.
     */
    public function __construct()
    {
        parent::__construct('UPDATE');
    }

    /**
     * Sets the table being updated.
     *
     * @param string $table The table to update.
     * @return $this
     */
    public function table($table)
    {
        $this->setOption('table', $table);

        return $this;
    }

    /**
     * Sets the columns and their values to be updated to.
     *
     * @param array $values An array with the key being the column name and the value being the value to set the column
     *                      to.
     * @return $this
     */
    public function set(array $values)
    {
        $this->setOption('set', $values);

        return $this;
    }

    /**
     * The WHERE clause for the UPDATE.
     *
     * @param string $where The condition for updating the table.
     * @return $this
     */
    public function where($where)
    {
        $this->setOption('where', $where);

        return $this;
    }

    /**
     * Sets the maximum number of rows to update. Please note not all RDBMS support this (PostgreSQL, SQLite3).
     *
     * @param int $limit The maximum number of rows to update.
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
