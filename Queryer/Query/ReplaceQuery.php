<?php
namespace Queryer\Query;

/**
 * Class ReplaceQuery
 * @package Queryer\Query
 */
class ReplaceQuery extends InsertQuery
{
    /**
     * Creates a REPLACE query builder.
     */
    public function __construct()
    {
        parent::__construct(true);
    }

    /**
     * Sets the column names that are primary/unique for the table that is having data replaced into. This is required
     * as some RDBMS do not actually support REPLACE, so in order to emulate it these column names are required.
     *
     * @param array $columnNames An array of the column names that are primary/unique (e.g. array('user_id')).
     * @return $this
     */
    public function keys(array $columnNames)
    {
        $this->setOption('keys', $columnNames);

        return $this;
    }
} 