<?php
namespace Queryer\Query;

/**
 * Class InsertQuery
 * @package Queryer\Query
 */
class InsertQuery extends QueryType
{
    public function __construct($isReplace = false)
    {
        parent::__construct($isReplace ? 'REPLACE' : 'INSERT');
    }

    public function ignore($ignore)
    {

    }

    public function into($table)
    {

    }

    /**
     * Can be called repeatedly.
     * @param $row
     */
    public function values($row)
    {

    }
}
