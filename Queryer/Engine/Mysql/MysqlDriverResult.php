<?php
namespace Queryer\Engine\Mysql;

use Queryer\Driver\DatabaseDriverResult;

/**
 * Class MysqlDriverResult
 * @package Rsvp\Database\Engine\Mysql
 */
class MysqlDriverResult extends DatabaseDriverResult
{
    /**
     * @var int
     */
    private $affectedRows;

    /**
     * @var int
     */
    private $insertId;

    /**
     * @var string
     */
    private $query;

    /**
     * @param \mysqli_result|bool $result
     * @param int $affectedRows
     * @param int $insertId
     * @param int $errorCode
     * @param string $errorMessage
     * @param string $query
     */
    public function __construct($result, $affectedRows, $insertId, $errorCode, $errorMessage, $query = null)
    {
        $this->affectedRows = $affectedRows;
        $this->insertId = $insertId;
        $this->query = $query;

        parent::__construct($result, $errorCode == 0 ? -1 : $errorCode, $errorMessage);
    }

    /**
     * @return int
     */
    public function getAffectedRows()
    {
        if (!is_bool($this->getResult()))
        {
            return 0;
        }

        return $this->affectedRows;
    }

    /**
     * @return int
     */
    public function getInsertId()
    {
        if (!is_bool($this->getResult()))
        {
            return 0;
        }

        return $this->insertId;
    }

    /**
     * @return int
     */
    public function getNumRows()
    {
        if (is_bool($this->getResult()))
        {
            return 0;
        }

        return $this->getResult()->num_rows;
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function seek($offset)
    {
        if (is_bool($this->getResult()))
        {
            return false;
        }

        return $this->getResult()->data_seek($offset);
    }

    /**
     * @return array|bool
     */
    public function fetchAssoc()
    {
        if (is_bool($this->getResult()))
        {
            return false;
        }

        return $this->getResult()->fetch_assoc();
    }

    /**
     * @return array|bool
     */
    public function fetchRow()
    {
        if (is_bool($this->getResult()))
        {
            return false;
        }

        return $this->getResult()->fetch_row();
    }

    /**
     * Returns the query that was executed to obtain this result object.
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        if (!is_bool($this->getResult()))
        {
            $this->getResult()->free();
        }
    }
}
