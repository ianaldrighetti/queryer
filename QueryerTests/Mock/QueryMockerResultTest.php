<?php
namespace QueryerTests\Mock;

use Queryer\Mock\QueryMockerResult;

/**
 * Class QueryMockerResultTest
 * @package QueryerTests\Mock
 */
class QueryMockerResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests getAffectedRows.
     */
    public function testGetAffectedRows()
    {
        $affectedRows = 23;
        $result = new QueryMockerResult(true, $affectedRows);
        $this->assertEquals($affectedRows, $result->getAffectedRows());
    }

    /**
     * Tests getInsertId.
     */
    public function testGetInsertId()
    {
        $insertId = 321;
        $result = new QueryMockerResult(true, 0, $insertId);
        $this->assertEquals($insertId, $result->getInsertId());
    }

    /**
     * Tests getNumRows.
     */
    public function testGetNumRows()
    {
        $rows = array(array(), array(), array());
        $result = new QueryMockerResult($rows);
        $this->assertEquals(count($rows), $result->getNumRows());
    }

    /**
     * Tests the getNumRows method with a boolean result.
     */
    public function testGetNumRowsBoolean()
    {
        $result = new QueryMockerResult(true);
        $this->assertEquals(0, $result->getNumRows());
    }

    /**
     * Tests the seeking method.
     */
    public function testSeek()
    {
        $rows = array(
            array(
                'user_id' => 1,
            ),
            array(
                'user_id' => 2,
            )
        );
        $result = new QueryMockerResult($rows);

        $result->seek(1);
        $this->assertEquals($rows[1], $result->fetchAssoc());

        $this->assertFalse($result->seek(count($rows)));
    }

    public function testSeekBoolean()
    {
        $result = new QueryMockerResult(true);
        $this->assertFalse($result->seek(0));
    }

    /**
     * Tests fetchRow.
     */
    public function testFetchRow()
    {
        $rows = array(
            array(
                'user_id' => 1,
            )
        );
        $result = new QueryMockerResult($rows);

        $this->assertEquals(array_values($rows[0]), $result->fetchRow());
    }

    /**
     * Tests fetchRow with a boolean result.
     */
    public function testFetchRowBoolean()
    {
        $result = new QueryMockerResult(true);

        $this->assertFalse($result->fetchRow());
    }

    /**
     * Tests the fetchAssoc method.
     */
    public function testFetchAssoc()
    {
        $rows = array(
            array(
                'user_id' => 1,
            ),
            array(
                'user_id' => 2,
            )
        );
        $result = new QueryMockerResult($rows);

        $this->assertEquals($rows[0], $result->fetchAssoc());
        $this->assertEquals($rows[1], $result->fetchAssoc());
        $this->assertFalse($result->fetchAssoc());
    }
}
 