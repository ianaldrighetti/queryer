<?php
namespace QueryerTests\Mocker;

use Queryer\Engine\Mysql\MysqliInterface;

/**
 * Class MockMysqli
 * @package QueryerTests\Mocker
 */
class MockMysqli extends MysqliInterface
{
    /**
     * Contains what setOptions was invoked with.
     * @var array
     */
    private $setOptions;

    /**
     * Contains what query was invoked with.
     * @var string
     */
    private $query;

    /**
     * Contains what the query method will return.
     * @var mixed
     */
    private $queryReturn;

    /**
     * Contains what real_escape_string was invoked with.
     * @var string
     */
    private $realEscapeString;

    /**
     * Initializes everything.
     */
    public function __construct()
    {
        $this->setOptions = null;
        $this->query = null;
        $this->realEscapeString = null;
        $this->affected_rows = 0;
        $this->insert_id = 0;
        $this->errno = 0;
        $this->error = null;
    }

    /**
     * Sets the options for connecting to the database.
     *
     * @param array $options
     * @return void
     */
    public function setOptions(array $options)
    {
        $this->setOptions = $options;
    }

    /**
     * Returns what the setOptions method was invoked with.
     *
     * @return array|null
     */
    public function getSetOptionsInvokedWith()
    {
        return $this->setOptions;
    }

    /**
     * Executes the query against the database.
     *
     * @param string $query
     * @return mixed
     */
    public function query($query)
    {
        $this->query = $query;

        return $this->queryReturn;
    }

    /**
     * Returns what the query method was invoked with.
     *
     * @return null|string
     */
    public function getQueryInvokedWith()
    {
        return $this->query;
    }

    /**
     * Sets what the query method should return when invoked.
     *
     * @param mixed $returnValue
     */
    public function setQueryReturn($returnValue)
    {
        $this->queryReturn = $returnValue;
    }

    /**
     * Escapes the string for safe insertion into a query.
     *
     * @param string $str
     * @return mixed
     */
    public function real_escape_string($str)
    {
        $this->realEscapeString = $str;

        return addcslashes($str, "'");
    }

    /**
     * Returns what the real_escape_string method was invoked with.
     *
     * @return null|string
     */
    public function getRealEscapeStringInvokedWith()
    {
        return $this->realEscapeString;
    }
}