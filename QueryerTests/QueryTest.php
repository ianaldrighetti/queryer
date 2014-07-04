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
     * Ensures that a SELECT query is properly built.
     */
    public function testSelectQuery()
    {
        $query = Query::select();

        $options = array(
            'type' => 'SELECT',
            'distinct' => true,
            'expr' => 'jt.user_id, myt.user_name',
            'table' => 'mytable',
            'alias' => 'myt',
            'joins' => array(
                array(
                    'type' => 'RIGHT',
                    'table' => 'joinedtable',
                    'alias' => 'jt',
                    'condition' => 'jt.user_id = myt.user_id',
                )
            ),
            'condition' => 'jt.user_id > 0',
            'groupBy' => 'jt.user_name ASC',
            'having' => 'COUNT(*) > 1',
            'orderBy' => 'jt.user_id ASC',
            'limit' => 10,
            'offset' => 20,
            'variables' => array(
                'myVar' => 100,
                'anotherVar' => 'string',
            )
        );

        $query->isDistinct($options['distinct'])
            ->expr($options['expr'])
            ->from($options['table'], $options['alias'])
            ->join($options['joins'][0]['type'], $options['joins'][0]['table'], $options['joins'][0]['alias'], $options['joins'][0]['condition'])
            ->where($options['condition'])
            ->groupBy($options['groupBy'])
            ->having($options['having'])
            ->orderBy($options['orderBy'])
            ->limit($options['limit'])
            ->offset($options['offset'])
            ->replace($options['variables']);

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
        $query = Query::update();

        $options = array(
            'type' => 'UPDATE',
            'table' => 'updateTable',
            'set' => array(
                'user_id' => 'user_id + 1',
                'user_name' => '\'name\''
            ),
            'condition' => 'user_id = 100',
            'limit' => 1,
        );

        $query->table($options['table'])
            ->set($options['set'])
            ->where($options['condition'])
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
        $query = Query::replace();

        $options = array(
            'type' => 'REPLACE',
            'table' => 'replaceTable',
            'rows' => array(
                array(
                    'user_id' => 1,
                    'user_name' => 'My Name',
                )
            ),
            'keys' => array('user_id')
        );

        $query->into($options['table'])
            ->values($options['rows'][0])
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
