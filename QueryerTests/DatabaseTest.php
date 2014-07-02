<?php
namespace QueryerTests;

use Queryer\Database;
use Queryer\Exception\DatabaseException;

/**
 * Class DatabaseTest
 * @package QueryerTests
 */
class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Cleans up Database after each test.
     */
    public function tearDown()
    {
        Database::clearInstance();
        Database::setDriverClassName(null);
        Database::setEngineOptions(array());
        Database::setEngineName(null);
    }

    /**
     * Tests setting the engine name.
     */
    public function testSetEngineName()
    {
        $engineName = 'MySQL';

        Database::setEngineName($engineName);
        $this->assertEquals($engineName, Database::getEngineName());
    }

    /**
     * Tests setting the engine options.
     */
    public function testSetEngineOptions()
    {
        $engineOptions = array(
            'host' => 'localhost',
            'user' => 'root',
            'pwd' => 'password',
            'db_name' => 'database',
        );

        Database::setEngineOptions($engineOptions);
        $this->assertEquals($engineOptions, Database::getEngineOptions());
    }

    /**
     * Tests getting the driver class's name.
     */
    public function testGetDriverClassName()
    {
        $engineName = 'MySQL';

        $this->assertEquals('\\Queryer\\Engine\\Mysql\\MysqlDriver', Database::getDriverClassName($engineName));
    }

    /**
     * Tests setting a custom driver class name.
     */
    public function testSetDriverClassName()
    {
        $driverClassName = '\\My\\Driver\\Class';

        Database::setDriverClassName($driverClassName);
        $this->assertEquals($driverClassName, Database::getDriverClassName('Mock'));
    }

    /**
     * Returns an instance of the MockDatabaseDriver.
     *
     * @param string $engineName
     * @param array $engineOptions
     * @return Database
     * @see \QueryerTests\Mock\MockDatabaseDriver
     */
    public function getMockDatabase($engineName, array $engineOptions)
    {
        Database::setEngineName($engineName);
        Database::setEngineOptions($engineOptions);
        Database::setDriverClassName('\\QueryerTests\\Mocker\\MockDatabaseDriver');

        return Database::getInstance();
    }

    /**
     * Tests getting an instance of the Database class.
     */
    public function testGetInstance()
    {
        $this->assertInstanceOf('\\Queryer\\Database', $this->getMockDatabase('', array()));
    }

    /**
     * Tests to ensure that the getDriverClassName method throws an Exception when no engine is set.
     *
     * @expectedException \Queryer\Exception\DatabaseException
     * @expectedExceptionCode \Queryer\Exception\DatabaseException::ENGINE_NOT_SPECIFIED
     */
    public function testGetDriverClassNameEngineNotSetException()
    {
        Database::getDriverClassName(null);
    }

    /**
     * Tests to ensure that the getInstance method throws an Exception when no engine is set.
     *
     * @expectedException \Queryer\Exception\DatabaseException
     * @expectedExceptionCode \Queryer\Exception\DatabaseException::ENGINE_NOT_SPECIFIED
     */
    public function testGetInstanceEngineNotSetException()
    {
        Database::getInstance();
    }

    /**
     * Tests to ensure that a database exception is thrown when the engine doesn't exist.
     */
    public function testGetInstanceException()
    {
        $engineName = 'DoesNotExist';
        $this->setExpectedException(
            '\\Queryer\\Exception\\DatabaseException',
            sprintf(
                'The database driver %s was not found (attempted to autoload "%s").',
                htmlspecialchars(ucfirst(strtolower($engineName))),
                htmlspecialchars(Database::getDriverClassName($engineName))
            ),
            DatabaseException::DRIVER_NOT_FOUND
        );

        // Tell it to use a nonexistent engine.
        Database::setEngineName($engineName);

        // Then it should throw an Exception.
        Database::getInstance();
    }

    /**
     * Tests to ensure the connect method is invoked.
     */
    public function testConnect()
    {
        $engineOptions = array(
            'host' => 'localhost',
            'user' => 'root',
            'pwd' => 'password',
            'db_name' => 'db',
        );

        $db = $this->getMockDatabase('Mock', $engineOptions);

        /** @var \QueryerTests\Mocker\MockDatabaseDriver $driver */
        $driver = $db->getDriver();

        // The connect method should have been called.
        $this->assertEquals($engineOptions, $driver->getConnectInvokedWith());
    }

    /**
     * Tests to ensure the connect method returns true when the connection is successful.
     */
    public function testConnectReturn()
    {
        Database::setEngineName('Mock');
        Database::setEngineOptions(array());
        Database::setDriverClassName('\\QueryerTests\\Mocker\\MockDatabaseDriver');

        $this->assertTrue(Database::connect());
    }

    /**
     * Tests to ensure the connect method throws an Exception when an error occurs.
     *
     * @expectedException \Queryer\Exception\DatabaseException
     * @expectedExceptionCode \Queryer\Exception\DatabaseException::ENGINE_NOT_SPECIFIED
     */
    public function testConnectException()
    {
        Database::setDriverClassName('\\QueryerTests\\Mocker\\MockDatabaseDriver');

        Database::connect();
    }

    /**
     * Tests to ensure that when sanitize is called, it invokes the driver's sanitize method.
     */
    public function testSanitize()
    {
        $db = $this->getMockDatabase('Mock', array());

        // Invoke sanitize.
        $str = 'Sanitize me!!!';
        $db->sanitize($str);

        /** @var \QueryerTests\Mocker\MockDatabaseDriver $driver */
        $driver = $db->getDriver();

        $this->assertEquals($str, $driver->getSanitizeInvokedWith());
    }

    /**
     * Tests to ensure that when execute is called, it invokes the driver's execute method.
     */
    public function testExecute()
    {
        $db = $this->getMockDatabase('Mock', array());

        // Invoke execute.
        $execute = array(
            'type' => 'SELECT',
            'select_expr' => '...',
        );
        $db->execute($execute);

        /** @var \QueryerTests\Mocker\MockDatabaseDriver $driver */
        $driver = $db->getDriver();

        $this->assertEquals($execute, $driver->getExecuteInvokedWith());
    }

    /**
     * Tests to ensure that when getTimestamp is called, it invokes the driver's getTimestamp method.
     */
    public function testGetTimestamp()
    {
        $db = $this->getMockDatabase('Mock', array());

        // Invoke getTimestamp.
        $timestamp = 482190;
        $db->getTimestamp($timestamp);

        /** @var \QueryerTests\Mocker\MockDatabaseDriver $driver */
        $driver = $db->getDriver();

        $this->assertEquals($timestamp, $driver->getGetTimestampInvokedWith());
    }

    /**
     * Tests to ensure that auto escaping is disabled by default.
     */
    public function testSetAutoEscapeDefault()
    {
        $this->assertFalse(Database::getAutoEscape());
    }

    /**
     * Tests to ensure setting the auto escape option works.
     */
    public function testSetAutoEscape()
    {
        Database::setAutoEscape(true);
        $this->assertTrue(Database::getAutoEscape());

        // Set it back to the default.
        Database::setAutoEscape(false);
    }
}
