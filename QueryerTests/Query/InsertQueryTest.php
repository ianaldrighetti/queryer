<?php
namespace QueryerTests\Query;

use Queryer\Query\InsertQuery;
use Queryer\Query;

/**
 * Class InsertQueryTest
 * @package QueryerTests\Query
 */
class InsertQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InsertQuery
     */
    private $insertQuery;

    /**
     * Sets up the InsertQuery instance.
     */
    public function setUp()
    {
        $this->insertQuery = Query::insert();
    }

    /**
     * Tests building an insert query.
     */
    public function testBuildInsertQuery()
    {
        $table = 'my_table';
        $ignore = false;
        $valueRow1 = array(
            'user_id' => 1,
            'user_name' => 'you',
        );
        $valueRow2 = array(
            'user_id' => 2,
            'user_name' => 'me',
        );

        $this->insertQuery
            ->ignore($ignore)
            ->into($table)
            ->values($valueRow1)
            ->values($valueRow2);

        $options = $this->insertQuery->getOptions();

        $this->assertArrayHasKey('table', $options);
        $this->assertEquals($table, $options['table']);

        $this->assertArrayHasKey('ignore', $options);
        $this->assertEquals($ignore, $options['ignore']);

        $this->assertArrayHasKey('rows', $options);
        $this->assertEquals(2, count($options['rows']));
        $this->assertEquals($valueRow1, $options['rows'][0]);
        $this->assertEquals($valueRow2, $options['rows'][1]);
    }
}
 