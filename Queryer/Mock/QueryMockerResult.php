<?php
namespace Queryer\Mock;

use Queryer\Driver\DatabaseDriverResult;

/**
 * Class QueryMockerResult
 *
 * This implements the actual DatabaseDriverResult abstract class and allows Query Mocker's to return an object that
 * appears like a result set to the application without it knowing it, well, really isn't. The mock result can be
 * supplied an array to be iterated over to provide results, for example.
 *
 * @package Queryer\Mock
 */
class QueryMockerResult extends DatabaseDriverResult
{
    /**
     * The number of rows affected by the query.
     * @var int
     */
    private $affectedRows;

    /**
     * The insert ID that resulted from this query.
     * @var int
     */
    private $insertId;

    /**
     * The current position we are at in the result array.
     * @var int
     */
    private $currentIndex;

    /**
     * The last index of the result array.
     * @var int
     */
    private $maxIndex;

    /**
     * The array of query options.
     * @var array
     */
    private $queryOptions;

    /**
     * Initializes the Mock Driver Result with results and information.
     *
     * @param array|bool $result Either an array (containing child arrays that will act as rows [column name => value]).
     *                           It can also be a boolean, where false indicates that the query was not successful and
     *                           true is for UPDATE, INSERT, REPLACE and DELETE commands being successful.
     * @param int $affectedRows The number of rows affected by the query.
     * @param int $insertId The last generated ID.
     * @param int $errorCode The error code.
     * @param string $errorMessage The error message.
     * @param array $queryOptions The array of query options.
     */
    public function __construct(
            $result,
            $affectedRows = 0,
            $insertId = 0,
            $errorCode = -1,
            $errorMessage = null,
            array $queryOptions = array()
        )
    {
        $this->affectedRows = $affectedRows;
        $this->insertId = $insertId;
        $this->queryOptions = $queryOptions;

        // Set up our index range.
        $this->currentIndex = 0;
        $this->maxIndex = is_array($result) ? count($result) - 1 : 0;

        parent::__construct($result, $errorCode, $errorMessage);
    }

    /**
     * Returns the number of rows affected by the query.
     *
     * @return int
     */
    public function getAffectedRows()
    {
        return $this->affectedRows;
    }

    /**
     * Returns the insert ID generated by the query.
     *
     * @return int
     */
    public function getInsertId()
    {
        return $this->insertId;
    }

    /**
     * The number of rows in the result set.
     *
     * @return int
     */
    public function getNumRows()
    {
        // We won't return anything more than 0 if the result is a boolean (no result set).
        if (is_bool($this->getResult()))
        {
            return 0;
        }

        return $this->maxIndex + 1;
    }

    /**
     * Seeks to the specified offset in the result set.
     *
     * @param int $offset
     * @return bool
     */
    public function seek($offset)
    {
        // We may not be able to seek anyways.
        if (is_bool($this->getResult()))
        {
            return false;
        }

        // Better be an integer!
        $offset = (int)$offset;
        if ($offset < 0 || $offset > $this->maxIndex)
        {
            return false;
        }

        $this->currentIndex = $offset;

        return true;
    }

    /**
     * Returns an array containing the result (with numerical indexes), or false if there are no more rows.
     *
     * @return array|bool
     */
    public function fetchRow()
    {
        // We will just use the fetchAssoc method.
        $result = $this->fetchAssoc();

        if (is_bool($result))
        {
            return $result;
        }

        // Just return the values indexed by numbers.
        return array_values($result);
    }

    /**
     * Returns an array containing the result (with the column names as keys), or false if there are no more rows.
     *
     * @return array|bool
     */
    public function fetchAssoc()
    {
        if (is_bool($this->getResult()))
        {
            return false;
        }

        // Get our result
        $result = $this->getResult();

        // Make sure we aren't out of range.
        if ($this->currentIndex > $this->maxIndex || !isset($result[$this->currentIndex]))
        {
            return false;
        }

        // Return the entry and increment the current index by one.
        return $result[$this->currentIndex++];
    }

    /**
     * Returns the array of query options.
     *
     * @return array
     */
    public function getQuery()
    {
        return $this->queryOptions;
    }
}
