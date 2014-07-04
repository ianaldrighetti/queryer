<?php
namespace QueryerTests\Engine\Mysql;

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
        $this->mysqlDriver->connect(array(
            'host' => '',
            'user' => '',
            'pwd' => '',
            'db_name' => '',
        ));
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
     * Tests to ensure that if no password option is supplied it will default to empty ('').
     */
    public function testConnectWithoutPwd()
    {
        $options = array(
            'host' => 'localhost',
            'user' => 'me',
            'db_name' => 'database',
        );

        $this->assertTrue($this->mysqlDriver->connect($options));

        $invokedOptions = $this->mysqliMocker->getSetOptionsInvokedWith();
        $this->assertArrayHasKey('pwd', $invokedOptions);
        $this->assertEquals('', $invokedOptions['pwd']);
    }

    /**
     * Tests to ensure that the connect method throws an exception when there are missing options.
     *
     * @expectedException \Queryer\Exception\DatabaseException
     */
    public function testConnectMissingOptionsException()
    {
        // Just give it one.
        $options = array(
            'host' => 'localhost',
        );

        $this->mysqlDriver->connect($options);
    }

    /**
     * Tests the execute method.
     */
    public function testExecute()
    {
        $query = Query::select()
            ->expr('user_id')
            ->from('users')
            ->where('user_id = {int:user_id}')
            ->replace(array(
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
        $query = Query::select()
            ->expr('mycolumn, anothercolumn')
            ->from('mytable', 'myt')
            ->join('INNER', 'users', 'u', 'u.user_id = myt.user_id')
            ->where('u.user_id = 1')
            ->groupBy('myt.user_group')
            ->having('myt.user_id > 1')
            ->orderBy('myt.user_id DESC')
            ->limit(20)
            ->offset(60);

        $result = MysqlDriver::generateQuery($query->getOptions());

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
        $query = Query::update()
            ->table('tabletoupdate')
            ->set(array(
                'myName' => '\'newName\''
            ))
            ->where('myUserId = 100')
            ->limit(2);

        $result = MysqlDriver::generateQuery($query->getOptions());

        $this->assertEquals('
        UPDATE tabletoupdate
        SET myName = \'newName\'
        WHERE myUserId = 100
        LIMIT 2', $result);
    }

    /**
     * Tests to ensure the MySQL Driver properly builds INSERT statements.
     */
    public function testInsertorReplaceQuery()
    {
        $query = Query::insert()
            ->ignore(true)
            ->into('mytable')
            ->values(array(
                'user_id' => 1,
                'user_name' => '\'username\'',
            ))
            ->values(array(
                'user_id' => 2,
                'user_name' => '\'another name\''
            ));

        $result = MysqlDriver::generateQuery($query->getOptions());

        $this->assertEquals('
        INSERT IGNORE INTO mytable
        (`user_id`, `user_name`)
        VALUES(1, \'username\'),(2, \'another name\')', $result);

        // Now with a REPLACE.
        $options = $query->getOptions();
        $options['type'] = 'REPLACE';
        $result = MysqlDriver::generateQuery($options);

        $this->assertEquals('
        REPLACE INTO mytable
        (`user_id`, `user_name`)
        VALUES(1, \'username\'),(2, \'another name\')', $result);
    }

    /**
     * @expectedException \Queryer\Exception\DatabaseException
     * @expectedExceptionMessage Row keys do not match, found at row values 2, 3 and 4.
     * @expectedExceptionCode \Queryer\Exception\DatabaseException::INVALID_QUERY
     */
    public function testInsertRowsDoNotMatchException()
    {
        $query = Query::insert()
            ->ignore(true)
            ->into('mytable')
            ->values(array(
                'user_id' => 1,
                'user_name' => '\'username\''
            ))
            ->values(array(
                'user_id' => 2
            ))
            ->values(array(
                'random' => 'yup'
            ))
            ->values(array(
                'mismatch' => 'yes'
            ));

        MysqlDriver::generateQuery($query->getOptions());
    }

    /**
     * Tests to ensure DELETE queries are built properly.
     */
    public function testDeleteQuery()
    {
        $query = Query::delete()
            ->from('mytable')
            ->where('user_id > 3')
            ->orderBy('user_id DESC')
            ->limit(100);

        $result = MysqlDriver::generateQuery($query->getOptions());

        $this->assertEquals('
        DELETE FROM mytable
        WHERE user_id > 3
        ORDER BY user_id DESC
        LIMIT 100', $result);
    }
}
 