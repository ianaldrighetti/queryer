<?php
namespace QueryerTests\Driver;

use Queryer\Driver\DatabaseTools;
use QueryerTests\DatabaseTest;

/**
 * Class DatabaseToolsTest
 * @package QueryerTests\Driver
 */
class DatabaseToolsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Sets up the tests.
     */
    public function setUp()
    {
        $dbTest = new DatabaseTest();

        DatabaseTools::setDatabaseInstance($dbTest->getMockDatabase(null, array()));
    }

    /**
     * Ensures that variables are properly replaced.
     *
     * @param string $type
     * @param mixed $value
     * @param string $expected
     * @dataProvider provideReplacements
     */
    public function testReplacements($type, $value, $expected)
    {
        $result = DatabaseTools::replaceVariables('{'. $type. ':variableName}', array(
            'variableName' => $value,
        ));

        $this->assertEquals($expected, $result);
    }

    /**
     * Provides data for the testReplacements test.
     *
     * @return array
     */
    public function provideReplacements()
    {
        return array(
            array('int', 1, '1'),
            array('double', 1.1, '1.1'),
            array('string', 'my string', '\'my string\''),
            array('string', 'it\'s me!', '\'it&#039;s me!\''),
            array('raw', 'raw', 'raw'),
            array('array_int', array(1, 2, 3), '1, 2, 3'),
            array('array_double', array(1.1, 1.5, 2.7), '1.1, 1.5, 2.7'),
            array('array_string', array('some', 'strings'), '\'some\', \'strings\'')
        );
    }

    /**
     * Tests a bad replacement call.
     *
     * @param string $type
     * @param mixed $value
     * @dataProvider provideBadReplacements
     * @expectedException \Queryer\Exception\DatabaseException
     * @expectedExceptionCode \Queryer\Exception\DatabaseException::TYPE_MISMATCH
     */
    public function testBadReplacements($type, $value)
    {
        DatabaseTools::replaceVariables('{'. $type. ':badVariableName}', array(
            'badVariableName' => $value,
        ));
    }

    /**
     * Provides data to the testBadReplacements method.
     *
     * @return array
     */
    public function provideBadReplacements()
    {
        return array(
            array('int', 'I\'m actually a string!!!'),
            array('double', 'Me too!'),
            array('array_int', 123),
            array('array_double', 'string...'),
            array('array_string', 'just a string, not an array')
        );
    }

    /**
     * Tests to ensure a DatabaseException is thrown when a variable is not defined.
     *
     * @expectedException \Queryer\Exception\DatabaseException
     * @expectedExceptionCode \Queryer\Exception\DatabaseException::UNDEFINED_VARIABLE
     */
    public function testMissingVariable()
    {
        DatabaseTools::replaceVariables('{int:imnotthevariableyouwant}', array(
            'notdefined' => 1,
        ));
    }

    /**
     * Tests to ensure a DatabaseException is thrown when an unknown datatype is specified.
     *
     * @expectedException \Queryer\Exception\DatabaseException
     * @expectedExceptionCode \Queryer\Exception\DatabaseException::UNKNOWN_DATATYPE
     */
    public function testUnknownType()
    {
        DatabaseTools::replaceVariables('{someunknowntype:imnotthevariableyouwant}', array(
            'imnotthevariableyouwant' => 1,
        ));
    }
}
