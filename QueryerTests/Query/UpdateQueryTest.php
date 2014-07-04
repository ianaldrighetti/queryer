<?php
namespace QueryerTests\Query;
use Queryer\Query\UpdateQuery;

/**
 * Class UpdateQueryTest
 * @package QueryerTests\Query
 */
class UpdateQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateQuery
     */
    private $updateQuery;

    /**
     * Creates an instance of UpdateQuery for testing.
     */
    public function setUp()
    {
        $this->updateQuery = new UpdateQuery();
    }

    /**
     * Tests building an UPDATE query.
     */
    public function testBuildUpdateQuery()
    {
        $table = 'my_table';
        $set = array(
            'col1' => 'col1 + col1',
            'value1' => '{int:val}',
        );
        $where = 'col1 > 100';
        $limit = 321;

        $this->updateQuery
            ->table($table)
            ->set($set)
            ->where($where)
            ->limit($limit);

        $options = $this->updateQuery->getOptions();
        $keyMap = array(
            'table', 'set', 'where', 'limit'
        );
        foreach ($keyMap as $key)
        {
            $this->assertArrayHasKey($key, $options);
            $this->assertEquals($$key, $options[$key]);
        }
    }
}
 