<?php
/**
 * Created by PhpStorm.
 * User: Ian
 * Date: 7/2/14
 * Time: 5:38 PM
 */

namespace Queryer\Query;

/**
 * Class ReplaceQuery
 * @package Queryer\Query
 */
class ReplaceQuery extends InsertQuery
{
    public function __construct()
    {
        parent::__construct(true);
    }

    public function keys($columnNames)
    {

    }
} 