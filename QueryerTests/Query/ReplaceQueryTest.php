<?php
namespace QueryerTests\Query;
use Queryer\Query\ReplaceQuery;

/**
 * Class ReplaceQueryTest
 * @package QueryerTests\Query
 */
class ReplaceQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReplaceQuery
     */
    private $replaceQuery;

    /**
     * Creates an instance of ReplaceQuery for testing.
     */
    public function setUp()
    {
        $this->replaceQuery = new ReplaceQuery();
    }

    /**
     * Tests building a REPLACE query.
     */
    public function testBuildReplaceQuery()
    {
        $ignore = true;
        $table = 'table_of_awesome';
        $values = array(
            'col' => 'data',
            'another_col' => 'even more data',
        );
        $keys = array('user', 'name');

        $this->replaceQuery
            ->ignore($ignore)
            ->into($table)
            ->values($values)
            ->keys($keys);

        $options = $this->replaceQuery->getOptions();
        $keyMap = array(
            'table' => $table,
            'ignore' => $ignore,
            'rows' => array($values),
            'keys' => $keys,
        );

        foreach ($keyMap as $key => $expectedValue)
        {
            $this->assertArrayHasKey($key, $options);
            $this->assertEquals($expectedValue, $options[$key]);
        }
    }
}
 