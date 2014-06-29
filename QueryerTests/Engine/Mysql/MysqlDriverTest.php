<?php
namespace QueryerTests\Engine\Mysql;

error_reporting(E_ALL);
ini_set('display_errors', 'On');

use Queryer\Driver\DatabaseTools;
use Queryer\Engine\Mysql\MysqlDriver;
use Queryer\Query;
use QueryerTests\Mocker\MockMysqli;

/**
 * Class MysqlDriverTest
 * @package QueryerTests\Engine\Mysql
 */
class MysqlDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The instance of MockMysqli.
     * @var \QueryerTests\Mocker\MockMysqli
     */
    private $mysqliMocker;

    /**
     * An instance of the MysqlDriver.
     * @var MysqlDriver
     */
    private $mysqlDriver;

    /**
     * Sets up the MockMysqli and MysqlDriver.
     */
    public function setUp()
    {
        $this->mysqliMocker = new MockMysqli();
        MysqlDriver::setMysqliInstance($this->mysqliMocker);

        $this->mysqlDriver = new MysqlDriver();
        $this->mysqlDriver->connect(array());
    }

    /**
     * Cleans up after each test.
     */
    public function tearDown()
    {
        MysqlDriver::clearMysqliInstance();
    }

    /**
     * Tests the connect method.
     */
    public function testConnect()
    {
        $options = array(
            'host' => 'localhost',
            'user' => 'me',
            'pwd' => 'dkjsal',
            'db_name' => 'database',
        );

        // Make sure connect returns true.
        $this->assertTrue($this->mysqlDriver->connect($options));

        // And that it was passed the options.
        $this->assertEquals($options, $this->mysqliMocker->getSetOptionsInvokedWith());
    }

    /**
     * Tests the execute method.
     */
    public function testExecute()
    {
        $query = Query::create('SELECT')
            ->selectExpr('user_id')
            ->from('users')
            ->where('user_id = {int:user_id}')
            ->variables(array(
                'user_id' => 100,
            ));

        // Set everything...
        $this->mysqliMocker->affected_rows = 100;
        $this->mysqliMocker->insert_id = 321;
        $this->mysqliMocker->errno = 322;
        $this->mysqliMocker->error = "ERROR";

        $userId = 32143;
        $this->mysqliMocker->setQueryReturn(true);

        $result = $this->mysqlDriver->execute($query->getOptions());

        $this->assertEquals(
            DatabaseTools::replaceVariables(
                MysqlDriver::generateQuery($query->getOptions()),
                array(
                    'user_id' => 100
                )
            ),
            $this->mysqliMocker->getQueryInvokedWith()
        );

        $this->assertEquals($this->mysqliMocker->affected_rows, $result->getAffectedRows());
        $this->assertEquals($this->mysqliMocker->insert_id, $result->getInsertId());
        $this->assertEquals($this->mysqliMocker->errno, $result->getErrorCode());
        $this->assertEquals($this->mysqliMocker->error, $result->getErrorMessage());
    }

    /**
     * Tests the sanitize method.
     */
    public function testSanitize()
    {
        $str = 'Sanitize me!';
        $this->mysqlDriver->sanitize($str);

        $this->assertEquals($str, $this->mysqliMocker->getRealEscapeStringInvokedWith());
    }

    /**
     * Tests the getTimestamp method.
     */
    public function testGetTimestamp()
    {
        // A warning will occur, otherwise...
        date_default_timezone_set('UTC');

        $this->assertEquals(date('Y-m-d H:i:s', time()), $this->mysqlDriver->getTimestamp());

        $timestamp = 4832190;
        $this->assertEquals(date('Y-m-d H:i:s', $timestamp), $this->mysqlDriver->getTimestamp($timestamp));
    }

    /**
     * Tests to ensure the MySQL Driver generates SELECT queries properly.
     */
    public function testSelectQuery()
    {
        $options = array(
            'type' => 'SELECT',
            'select_expr' => 'mycolumn, anothercolumn',
            'from' => 'mytable',
            'from_alias' => 'myt',
            'joins' => array(
                array(
                    'type' => 'INNER',
                    'table' => 'users',
                    'table_alias' => 'u',
                    'condition' => 'u.user_id = myt.user_id',
                )
            ),
            'where_condition' => 'u.user_id = 1',
            'group_by' => 'myt.user_group',
            'having' => 'myt.user_id > 1',
            'order_by' => 'myt.user_id DESC',
            'limit' => 20,
            'offset' => 60,
        );

        $result = MysqlDriver::generateQuery($options);

        $this->assertEquals('
        SELECT
            mycolumn, anothercolumn
        FROM mytable AS myt
            INNER JOIN users AS u ON u.user_id = myt.user_id
        WHERE u.user_id = 1
        GROUP BY myt.user_group
        HAVING myt.user_id > 1
        ORDER BY myt.user_id DESC
        LIMIT 60, 20', $result);
    }

    /**
     * Tests to ensure the MySQL Driver generates UPDATE queries properly.
     */
    public function testUpdateQuery()
    {
        $options = array(
            'type' => 'UPDATE',
            'ignore' => true,
            'table' => 'tabletoupdate',
            'values' => 'myName = \'newName\'',
            'where_condition' => 'myUserId = 100',
            'order_by' => 'myName ASC',
            'limit' => 2,
        );

        $result = MysqlDriver::generateQuery($options);

        $this->assertEquals('
        UPDATE IGNORE tabletoupdate
        SET myName = \'newName\'
        WHERE myUserId = 100
        ORDER BY myName ASC
        LIMIT 2', $result);
    }

    /**
     * Tests to ensure the MySQL Driver properly builds INSERT statements.
     */
    public function testInsertorReplaceQuery()
    {
        $options = array(
            'type' => 'INSERT',
            'ignore' => true,
            'table' => 'mytable',
            'values' => array(
                'user_id' => 1,
                'user_name' => 'username',
            )
        );

        $result = MysqlDriver::generateQuery($options);

        $this->assertEquals('
        INSERT IGNORE INTO mytable
        (`user_id`, `user_name`)
        VALUES(1, username)', $result);
    }

    /**
     * Tests to ensure DELETE queries are built properly.
     */
    public function testDeleteQuery()
    {
        $options = array(
            'type' => 'DELETE',
            'from' => 'mytable',
            'where_condition' => '1 = 1',
            'order_by' => 'user_id DESC',
            'limit' => 100,
        );

        $result = MysqlDriver::generateQuery($options);

        $this->assertEquals('
        DELETE FROM mytable
        WHERE 1 = 1
        ORDER BY user_id DESC
        LIMIT 100', $result);
    }
}
 