<?php
namespace QueryerTests;

use Queryer\Mock\QueryMocker;
use Queryer\Query;

/**
 * Class QueryTest
 *
 * Tests the Query class.
 *
 * @package Queryer
 */
class QueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Ensures that the Query::create method returns an instance of a query.
     */
    public function testCreateMethod()
    {
        $query = Query::create('SELECT');

        $this->assertInstanceOf('\Queryer\Query', $query);
    }

    /**
     * Ensures that an InvalidArgumentException is thrown when an unknown type is supplied to the create method.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The query type UNKNOWN is unknown.
     */
    public function testInvalidArgumentException()
    {
        Query::create('Unknown');
    }

    /**
     * Ensures that a SELECT query is properly built.
     */
    public function testSelectQuery()
    {
        $query = Query::create('SELECT');

        $options = array(
            'type' => 'SELECT',
            'distinct' => true,
            'select_expr' => 'jt.user_id, myt.user_name',
            'from' => 'mytable',
            'from_alias' => 'myt',
            'joins' => array(
                array(
                    'type' => 'RIGHT',
                    'table' => 'joinedtable',
                    'table_alias' => 'jt',
                    'condition' => 'jt.user_id = myt.user_id',
                )
            ),
            'where_condition' => 'jt.user_id > 0',
            'group_by' => 'jt.user_name ASC',
            'having' => 'COUNT(*) > 1',
            'order_by' => 'jt.user_id ASC',
            'limit' => 10,
            'offset' => 20,
            'variables' => array(
                'myVar' => 100,
                'anotherVar' => 'string',
            )
        );

        $query->distinct($options['distinct'])
            ->selectExpr($options['select_expr'])
            ->from($options['from'])
            ->alias($options['from_alias'])
            ->join($options['joins'][0]['type'], $options['joins'][0]['table'], $options['joins'][0]['table_alias'], $options['joins'][0]['condition'])
            ->where($options['where_condition'])
            ->groupBy($options['group_by'])
            ->having($options['having'])
            ->orderBy($options['order_by'])
            ->limit($options['limit'])
            ->offset($options['offset'])
            ->variables($options['variables']);

        $queryOptions = $query->getOptions();
        foreach ($options as $key => $value)
        {
            $this->assertArrayHasKey($key, $queryOptions);
            $this->assertEquals($options[$key], $queryOptions[$key]);
        }
    }

    /**
     * Ensures that an UPDATE query is properly built.
     */
    public function testUpdateQuery()
    {
        $query = Query::create('UPDATE');

        $options = array(
            'type' => 'UPDATE',
            'ignore' => true,
            'table' => 'updateTable',
            'values' => 'user_id = user_id + 1, user_name = \'name\'',
            'where_condition' => 'user_id = 100',
            'order_by' => 'user_id DESC',
            'limit' => 1,
        );

        $query->ignore($options['ignore'])
            ->table($options['table'])
            ->values($options['values'])
            ->where($options['where_condition'])
            ->orderBy($options['order_by'])
            ->limit($options['limit']);

        $queryOptions = $query->getOptions();
        foreach ($options as $key => $value)
        {
            $this->assertArrayHasKey($key, $queryOptions);
            $this->assertEquals($options[$key], $queryOptions[$key]);
        }
    }

    /**
     * Ensures that a REPLACE query is properly built.
     */
    public function testReplaceQuery()
    {
        $query = Query::create('REPLACE');

        $options = array(
            'type' => 'REPLACE',
            'table' => 'replaceTable',
            'values' => array(
                'user_id' => 1,
                'user_name' => 'My Name',
            ),
            'keys' => array('user_id')
        );

        $query->table($options['table'])
            ->values($options['values'])
            ->keys($options['keys']);

        $queryOptions = $query->getOptions();
        foreach ($options as $key => $value)
        {
            $this->assertArrayHasKey($key, $queryOptions);
            $this->assertEquals($options[$key], $queryOptions[$key]);
        }
    }

    /**
     * Tests setting a query mocker.
     */
    public function testSetMocker()
    {
        $mocker = new QueryMocker();

        Query::setMocker($mocker);
        $this->assertEquals($mocker, Query::getMocker());
    }

    /**
     * Tests clearing a mocker.
     */
    public function testClearMocker()
    {
        $mocker = new QueryMocker();

        Query::setMocker($mocker);
        $this->assertNotNull(Query::getMocker());

        Query::clearMocker();
        $this->assertNull(Query::getMocker());
    }
}
