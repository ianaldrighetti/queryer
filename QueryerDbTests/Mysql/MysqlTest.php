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

            echo 'Connected to MySQL database.', "\n";
        }
        catch (\PDOException $ex)
        {
            echo 'Connection failed: '. $ex->getMessage();
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
     * Tests to ensure inserting data works.
     */
    public function testInsert()
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
                'user_status' => $row['user_stats']
            ))->execute();

        $this->assertTrue($result->success());
        $this->assertEquals($row['user_id'], $result->getInsertId());
    }
}
 