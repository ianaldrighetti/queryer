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

    public function isDistinct($isDistinct)
    {
        $this->setOption('distinct', !empty($isDistinct));
    }

    public function columns($expr)
    {
        $this->setOption('columns', $columns);
    }

    public function from($table, $alias = null)
    {

    }

    public function join($type, $table, $alias, $condition)
    {

    }

    public function where($condition)
    {

    }

    public function groupBy($groupBy)
    {

    }

    public function having($having)
    {

    }

    public function orderBy($orderBy)
    {

    }

    public function limit($limit)
    {

    }

    public function offset($offset)
    {

    }
}
