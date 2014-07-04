<?php
namespace Queryer\Query;

/**
 * Class InsertQuery
 * @package Queryer\Query
 */
class InsertQuery extends QueryType
{
    /**
     * Creates an INSERT or REPLACE query builder.
     *
     * @param bool $isReplace Whether this is an INSERT or REPLACE query.
     */
    public function __construct($isReplace = false)
    {
        parent::__construct($isReplace ? 'REPLACE' : 'INSERT');
    }

    /**
     * Indicates whether the IGNORE flag should be set.
     *
     * @param bool $ignore
     * @return $this
     */
    public function ignore($ignore)
    {
        $this->setOption('ignore', !empty($ignore));

        return $this;
    }

    /**
     * The table to insert the data into.
     *
     * @param string $table The table name.
     * @return $this
     */
    public function into($table)
    {
        $this->setOption('table', $table);

        return $this;
    }

    /**
     * Adds a row of values to be inserted into the table. This can be called multiple times to add more rows to the
     * insert query.
     *
     * @param array $row The row of data that is to be inserted into the table.
     * @return $this
     */
    public function values(array $row)
    {
        // Get the existing rows.
        $rows = $this->getOption('rows', array());

        // Add the row.
        $rows[] = $row;

        $this->setOption('rows', $rows);

        return $this;
    }
}
