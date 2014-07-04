<?php
namespace QueryerTests\Query;

use Queryer\Mock\QueryMocker;
use Queryer\Query\QueryType;

/**
 * Class QueryTypeTest
 * @package QueryerTests\Query
 */
class QueryTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QueryType
     */
    private $queryType;

    /**
     * Creates an instance of QueryType for testing.
     */
    public function setUp()
    {
        $this->queryType = new QueryType(null);
    }

    /**
     * Cleans up!
     */
    public function tearDown()
    {
        QueryType::clearMocker();
    }

    /**
     * Tests to ensure the query type indicated is saved.
     */
    public function testQueryTypeConstructor()
    {
        $type = 'myquerytype';
        $queryType = new QueryType($type);

        $options = $queryType->getOptions();

        $this->assertArrayHasKey('type', $options);
        $this->assertEquals(strtoupper($type), $options['type']);
    }

    /**
     * Tests to ensure setting a Query Mocker works.
     */
    public function testSetMocker()
    {
        $mocker = new QueryMocker();

        QueryType::setMocker($mocker);
        $this->assertEquals($mocker, QueryType::getMocker());
    }

    /**
     * Tests to ensure replace variables are saved.
     */
    public function testReplace()
    {
        $variables = array(
            'var1' => 2,
            'var2' => 1,
        );
        $this->queryType->replace($variables);

        $options = $this->queryType->getOptions();

        $this->assertArrayHasKey('variables', $options);
        $this->assertEquals($variables, $options['variables']);
    }

    /**
     * Tests to ensure that the Query Mocker is used to execute a query.
     */
    public function testExecuteMocker()
    {
        $mocker = new QueryMocker();
        $mocker->setResult(true);

        QueryType::setMocker($mocker);

        $this->assertInstanceOf('\\Queryer\\Mock\\QueryMockerResult', $this->queryType->execute());
    }
}
 