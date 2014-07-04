<?php
namespace QueryerTests\Query;

use Queryer\Query;

/**
 * Class DeleteQueryTest
 * @package QueryerTests\Query
 */
class DeleteQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Queryer\Query\DeleteQuery
     */
    private $deleteQuery;

    /**
     * Sets up the DeleteQuery instance.
     */
    public function setUp()
    {
        $this->deleteQuery = Query::delete();
    }

    /**
     * Tests building a DELETE query.
     */
    public function testBuildDeleteQuery()
    {
        $tableName = 'my_table';
        $where = 'user = anotherUser';
        $orderBy = 'user DESC';
        $limit = 321;

        $this->deleteQuery
            ->from($tableName)
            ->where($where)
            ->orderBy($orderBy)
            ->limit($limit);

        $options = $this->deleteQuery->getOptions();
        $keyMap = array(
            'table' => $tableName,
            'condition' => $where,
            'orderBy' => $orderBy,
            'limit' => $limit,
        );

        foreach ($keyMap as $key => $expectedValue)
        {
            $this->assertArrayHasKey($key, $options);
            $this->assertEquals($expectedValue, $options[$key]);
        }
    }
}
