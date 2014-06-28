<?php
namespace Queryer\Driver;

/**
 * Class DatabaseDriverResult
 *
 * The abstract class that is to be implemented by a Database Driver's result class.
 *
 * @package Queryer\Driver
 */
abstract class DatabaseDriverResult
{
    /**
     * The result of the query executed by a database driver.
     * @var mixed
     */
    private $result;

    /**
     * The error code that occurred when the query was executed, if any.
     * @var int
     */
    private $errorCode;

    /**
     * The error message that occurred when the query was executed, if any.
     * @var string
     */
    private $errorMessage;

    /**
     * Initializes the Database Driver Result with the result and error, if any.
     *
     * @param mixed $result The result, boolean expected for commands that don't return a result set.
     * @param int $errorCode The error code, if any.
     * @param string $errorMessage The error message, if any.
     */
    public function __construct($result, $errorCode = -1, $errorMessage = null)
    {
        $this->setResult($result);
        $this->setError($errorCode, $errorMessage);
    }

    /**
     * Sets the result.
     *
     * @param mixed $result
     */
    final protected function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * Returns the result.
     *
     * @return mixed
     */
    final protected function getResult()
    {
        return $this->result;
    }

    /**
     * Sets the error that occurred while executing the query, if any.
     *
     * @param int $errorCode
     * @param string $errorMessage
     */
    final protected function setError($errorCode = -1, $errorMessage = null)
    {
        $this->errorCode = (string)$errorCode;
        $this->errorMessage = !empty($errorMessage) && strlen($errorMessage) > 0 ? $errorMessage : null;
    }

    /**
     * Returns the error code that occurred when attempting to execute the query, if any.
     *
     * @return int
     */
    final public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Returns the error message that occurred when attempting to execute the query, if any.
     *
     * @return string
     */
    final public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Returns whether the query executed successfully.
     *
     * @return bool
     */
    final public function success()
    {
        return !empty($this->result);
    }

    /**
     * Returns the number of rows that were affected by the query.
     *
     * @return int
     */
    abstract public function getAffectedRows();

    /**
     * Returns the ID for the row inserted into a table with an auto incrementing column.
     *
     * @return int
     */
    abstract public function getInsertId();

    /**
     * Returns the number of rows in the result set.
     *
     * @return int
     */
    abstract public function getNumRows();

    /**
     * Moves the pointer of the result set to the specified offset.
     *
     * @param int $offset
     * @return bool
     */
    abstract public function seek($offset);

    /**
     * Returns the row (associative) or false if there are no more rows in the result set.
     *
     * @return array|bool
     */
    abstract public function fetchAssoc();

    /**
     * Returns the row (numeric index only) or false if there are no more rows in the result set.
     *
     * @return array|bool
     */
    abstract public function fetchRow();
}
