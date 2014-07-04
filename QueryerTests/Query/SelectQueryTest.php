<?php
namespace QueryerTests\Query;

use Queryer\Query\SelectQuery;
use Queryer\Query;

/**
 * Class SelectQueryTest
 * @package QueryerTests\Query
 */
class SelectQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SelectQuery
     */
    private $selectQuery;

    /**
     * Creates an instance of SelectQuery for testing.
     */
    public function setUp()
    {
        $this->selectQuery = Query::select();
    }

    /**
     * Tests building a SELECT query.
     */
    public function testBuildSelectQuery()
    {
        $distinct = true;
        $expr = 'user_id, user_name, user_status';
        $table = 'users';
        $alias = 'u';
        $condition = 'user_id > 0';
        $groupBy = 'user_status ASC';
        $having = 'user_status != 0';
        $orderBy = 'user_id DESC';
        $limit = 312;
        $offset = 432;
        $joinType = 'INNER';
        $joinTable = 'log';
        $joinAlias = 'l';
        $joinCondition = 'l.user_id = u.user_id';

        $this->selectQuery
            ->isDistinct($distinct)
            ->expr($expr)
            ->from($table, $alias)
            ->join($joinType, $joinTable, $joinAlias, $joinCondition)
            ->where($condition)
            ->groupBy($groupBy)
            ->having($having)
            ->orderBy($orderBy)
            ->limit($limit)
            ->offset($offset);

        $options = $this->selectQuery->getOptions();

        $keyMap = array(
            'distinct', 'expr', 'table', 'alias', 'condition', 'groupBy', 'having', 'orderBy', 'limit', 'offset'
        );
        foreach ($keyMap as $key)
        {
            $this->assertArrayHasKey($key, $options);
            $this->assertEquals($$key, $options[$key]);
        }

        // Check the JOIN.
        $this->assertArrayHasKey('joins', $options);
        $this->assertTrue(is_array($options['joins']));
        $this->assertEquals(1, count($options['joins']));

        $join = $options['joins'][0];
        $keyMap = array(
            'type' => $joinType,
            'table' => $joinTable,
            'alias' => $joinAlias,
            'condition' => $joinCondition,
        );
        foreach ($keyMap as $key => $expectedValue)
        {
            $this->assertArrayHasKey($key, $join);
            $this->assertEquals($expectedValue, $join[$key]);
        }
    }
}
 