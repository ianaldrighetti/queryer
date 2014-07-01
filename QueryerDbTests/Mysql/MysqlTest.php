<?php
namespace QueryerDbTests\Mysql;

use PDO;
use Queryer\Database;
use Queryer\Query;

/**
 * Class MysqlTest
 * @package QueryerDbTests\Mysql
 */
class MysqlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PDO
     */
    private $pdo;

    /**
     * Sets everything to null.
     */
    public function __construct()
    {
        $this->pdo = null;
    }

    /**
     * Sets up the connection information and creates a test table.
     */
    public function setUp()
    {
        Database::setEngineName('MySQL');

        // All these options are found in phpunit.mysql.xml.
        Database::setEngineOptions(array(
            'host' => $GLOBALS['db_host'],
            'user' => $GLOBALS['db_user'],
            'pwd' => $GLOBALS['db_pwd'],
            'db_name' => $GLOBALS['db_name']
        ));

        $this->setUpTestTable();
    }

    /**
     * Drops the test table.
     */
    public function tearDown()
    {
        $pdo = $this->getPdoObject();

        $pdo->query('DROP TABLE users');
    }

    /**
     * Sets up the test tables.
     */
    private function setUpTestTable()
    {
        $pdo = $this->getPdoObject();

        $pdo->query('
        CREATE TABLE users
        (
            user_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_name VARCHAR(40) NOT NULL,
            user_email VARCHAR(100) NOT NULL,
            user_status TINYINT(3) UNSIGNED NOT NULL DEFAULT \'0\',
            PRIMARY KEY (user_id)
        )');
    }

    /**
     * Returns an instance of a PDO object hooked up to the MySQL database.
     *
     * @return PDO
     */
    private function getPdoObject()
    {
        if (!is_null($this->pdo))
        {
            return $this->pdo;
        }

        try
        {
            $this->pdo = new PDO(
                'mysql:dbname=' . $GLOBALS['db_name'] . ';host=' . $GLOBALS['db_host'],
                $GLOBALS['db_user'],
                $GLOBALS['db_pwd']
            );
        }
        catch (\PDOException $ex)
        {
            echo "\n\n". 'Connection to MySQL server failed: '. $ex->getMessage();
            exit(255);
        }

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $this->pdo;
    }

    /**
     * Tests to ensure connecting to the database works.
     */
    public function testConnect()
    {
        $this->assertTrue(Database::connect());
    }

    /**
     * Tests to ensure inserting data works (along with retrieving that data as well).
     */
    public function testInsertAndSelect()
    {
        // The row of data (user_id is what we expect it's value to be, since it is AUTO INCREMENT).
        $row = array(
            'user_id' => 1,
            'user_name' => 'The user name',
            'user_email' => 'me@outlook.com',
            'user_status' => 2,
        );

        $result = Query::create('INSERT')
            ->table('users')
            ->values(array(
                'user_name' => '{string:user_name}',
                'user_email' => '{string:user_email}',
                'user_status' => '{int:user_status}',
            ))
            ->variables(array(
                'user_name' => $row['user_name'],
                'user_email' => $row['user_email'],
                'user_status' => $row['user_status']
            ))->execute();

        $this->assertTrue($result->success());
        $this->assertEquals($row['user_id'], $result->getInsertId());

        // Now select the data.
        $result = Query::create('SELECT')
            ->selectExpr('user_id, user_name, user_email, user_status')
            ->from('users')
            ->where('user_id = {int:user_id}')
            ->variables(array(
                'user_id' => $row['user_id']
            ))
            ->execute();

        // Fetch the row.
        $actualRow = $result->fetchAssoc();
        $this->assertTrue(is_array($actualRow));

        // Now make sure it is right.
        $this->assertEquals($row, $actualRow);
    }

    /**
     * Tests updating data.
     */
    public function testUpdate()
    {
        // Insert some fake data.
        $this->insertFakeData(3);

        $userName = 'better name';

        // Update the table data.
        $result = Query::create('UPDATE')
            ->table('users')
            ->values(array(
                'user_name' => '{string:user_name}',
            ))
            ->where('user_id = {int:user_id}')
            ->variables(array(
                'user_id' => 1,
                'user_name' => $userName,
            ))
            ->execute();

        var_dump($result->getErrorCode());
        var_dump($result->getErrorMessage());
        var_dump($result->getQuery());

        // Make sure it worked!
        $this->assertTrue($result->success());
        $this->assertEquals(1, $result->getAffectedRows());

        // Now we should select the data.
        $result = Query::create('SELECT')
            ->selectExpr('user_name')
            ->from('users')
            ->where('user_id = {int:user_id}')
            ->variables(array(
                'user_id' => 1
            ))
            ->execute();

        $this->assertTrue($result->success());

        // Make sure it was actually updated.
        $row = $result->fetchAssoc();
        $this->assertArrayHasKey('user_name', $row);
        $this->assertEquals($userName, $row['user_name']);
    }

    /**
     * Tests deleting data.
     */
    public function testDelete()
    {
        $this->insertFakeData(5);

        $result = Query::create('DELETE')
            ->from('users')
            ->where('user_id < {int:lt}')
            ->variables(array(
                'lt' => 3,
            ))
            ->execute();

        $this->assertTrue($result->success());
        $this->assertEquals(2, $result->getAffectedRows());
    }

    /**
     * Inserts fake data into the users table.
     *
     * @param int $amount The number of rows to insert.
     */
    private function insertFakeData($amount = 1)
    {
        if ((int)$amount < 1)
        {
            $amount = 1;
        }

        for ($i = 0; $i < $amount; $i++)
        {
            Query::create('INSERT')
                ->table('users')
                ->values(array(
                    'user_name' => '{string:user_name}',
                    'user_email' => '{string:user_email}',
                    'user_status' => '{int:user_status}',
                ))
                ->variables(array(
                    'user_name' => 'user'. ($i + 1),
                    'user_email' => 'user'. ($i + 1). '@outlook.com',
                    'user_status' => mt_rand(0, 2)
                ))->execute();
        }
    }
}
 