<?php
namespace RsvpTest\Database\Engine\Mysql;

use Queryer\Engine\Mysql\MysqlDriver;

/**
 * Class MysqlDriverTest
 * @package RsvpTest\Database\Engine\Mysql
 */
class MysqlDriverTest extends \PHPUnit_Framework_TestCase
{
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
 