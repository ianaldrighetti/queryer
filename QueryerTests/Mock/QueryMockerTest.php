<?php
namespace QueryerTests\Mock;

use Queryer\Mock\QueryMocker;

/**
 * Class QueryMockerTest
 * @package QueryerTests\Mock
 */
class QueryMockerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QueryMocker
     */
    private $queryMocker;

    /**
     * Sets up the Query Mocker to be used in tests.
     */
    public function setUp()
    {
        $this->queryMocker = new QueryMocker();
    }

    /**
     * Tests setDebug and getDebug.
     */
    public function testSetDebug()
    {
        $this->queryMocker->setDebug(true);
        $this->assertTrue($this->queryMocker->getDebug());
    }

    /**
     * Tests the setResult method to ensure that it always returns the same result each time a query is executed.
     */
    public function testSetResult()
    {
        $resultSet = array(
            array(
                'user_id' => 1,
                'user_name' => 'Me',
            ),
            array(
                'user_id' => 2,
                'user_name' => 'You',
            )
        );
        $affectedRows = 2;
        $insertId = 100;
        $errorCode = 504;
        $errorMessage = 'error!';

        // Set our result.
        $this->queryMocker->setResult($resultSet, $affectedRows, $insertId, $errorCode, $errorMessage);

        // Just get the result.
        $result = $this->queryMocker->execute(array());

        // Make sure we get an instance of a QueryMockerResult.
        $this->assertInstanceOf('\\Queryer\\Mock\\QueryMockerResult', $result);

        $this->assertEquals(count($resultSet), $result->getNumRows());
        $this->assertEquals($affectedRows, $result->getAffectedRows());
        $this->assertEquals($insertId, $result->getInsertId());
        $this->assertEquals($errorCode, $result->getErrorCode());
        $this->assertEquals($errorMessage, $result->getErrorMessage());

        // Check the rows.
        for ($i = 0; $i < count($resultSet); $i++)
        {
            $row = $resultSet[$i];

            $this->assertEquals($row, $result->fetchAssoc());
        }
    }

    /**
     * Tests the getExecutedCount, getExecuted and getLastExecuted methods.
     */
    public function testExecuted()
    {
        $options = array(
            'type' => 'SELECT',
            'select_expr' => '...',
        );
        $this->queryMocker->execute($options);

        $options2 = array(
            'type' => 'UPDATE',
            'values' => '...',
        );
        $this->queryMocker->execute($options2);

        // It should have logged them.
        $this->assertEquals(2, $this->queryMocker->getExecutedCount());

        // And saved them.
        $this->assertEquals($options, $this->queryMocker->getExecuted(0));
        $this->assertEquals($options2, $this->queryMocker->getExecuted(1));
        $this->assertTrue(
            $options2 === $this->queryMocker->getLastExecuted() &&
            $this->queryMocker->getLastExecuted() === $this->queryMocker->getExecuted(1)
        );

        // Test getExecuted with no parameters (should return them all).
        $this->assertEquals(
            array(
                $options, $options2
            ),
            $this->queryMocker->getExecuted()
        );
    }

    /**
     * Tests the addResult method and ensures that the results are returned in the right order.
     */
    public function testAddResult()
    {
        $resultSet1 = array(
            array(
                'user_id' => 1,
            ),
            array(
                'user_id' => 2,
            )
        );
        $insertId1 = 2;
        $this->queryMocker->addResult($resultSet1, 0, $insertId1);

        $resultSet2 = array(
            array(
                'name' => 'you',
            )
        );
        $affectedRows2 = 1;
        $this->queryMocker->addResult($resultSet2, $affectedRows2);

        // Let's see what the first one has in store for us.
        $result1 = $this->queryMocker->execute(array());

        $this->assertEquals(count($resultSet1), $result1->getNumRows());
        $this->assertEquals($insertId1, $result1->getInsertId());

        // Now the second.
        $result2 = $this->queryMocker->execute(array());

        $this->assertEquals(count($resultSet2), $result2->getNumRows());
        $this->assertEquals($affectedRows2, $result2->getAffectedRows());
    }

    /**
     * Tests to ensure that the execute method throws a QueryMockerResultException when there aren't enough results.
     *
     * @expectedException \Queryer\Exception\QueryMockerResultException
     * @expectedExceptionCode \Queryer\Exception\QueryMockerResultException::NOT_ENOUGH_RESULTS
     */
    public function testAddResultNotExpectingException()
    {
        $this->queryMocker->addResult(true);
        $this->queryMocker->execute(array());

        // This should result in an Exception.
        $this->queryMocker->execute(array());
    }
}
 