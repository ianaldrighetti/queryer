<?php
namespace Queryer\Mock;

use Queryer\Exception\QueryMockerResultException;

/**
 * Class QueryMocker
 *
 * A default implementation of the QueryMockerInterface. This is used to mock out database results for testing purposes.
 *
 * @package Queryer\Mock
 */
class QueryMocker implements QueryMockerInterface
{
    /**
     * The result to encapsulate in a QueryMockerResult.
     *
     * @var array|bool
     */
    private $result;

    /**
     * An array containing multiple result sets.
     * @var array
     */
    private $results;

    /**
     * An integer containing the current result position.
     *
     * @var int
     */
    private $resultIndex;

    /**
     * An array containing all executed queries.
     *
     * @var array
     */
    private $executed;

    /**
     * Whether to output all queries executed.
     * @var bool
     */
    private $debug;

    /**
     * Initializes the Query Mocker.
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Resets the query mocker.
     */
    public function reset()
    {
        $this->setResult(false);
        $this->results = null;
        $this->resultIndex = 0;
        $this->executed = array();
        $this->debug = false;
    }

    /**
     * Sets the result to be encapsulated into a QueryMockerResult.
     *
     * @param array|bool $result
     * @param int $affectedRows
     * @param int $insertId
     * @param int $errorCode
     * @param string $errorMessage
     */
    public function setResult($result, $affectedRows = 0, $insertId = 0, $errorCode = -1, $errorMessage = null)
    {
        $this->results = null;
        $this->resultIndex = 0;
        $this->result = array(
            'result' => $result,
            'affectedRows' => $affectedRows,
            'insertId' => $insertId,
            'errorCode' => $errorCode,
            'errorMessage' => $errorMessage,
        );
    }

    /**
     * Adds a result, these results will be returned in sequential order.
     *
     * @param mixed $result
     * @param int $affectedRows
     * @param int $insertId
     * @param int $errorCode
     * @param string $errorMessage
     */
    public function addResult($result, $affectedRows = 0, $insertId = 0, $errorCode = -1, $errorMessage = null)
    {
        $this->result = null;

        // If the results are null, make it an array.
        if (is_null($this->results))
        {
            $this->results = array();
        }

        // Add the result.
        $this->results[] = array(
            'result' => $result,
            'affectedRows' => $affectedRows,
            'insertId' => $insertId,
            'errorCode' => $errorCode,
            'errorMessage' => $errorMessage,
        );
    }

    /**
     * Returns the number of queries executed.
     *
     * @return int
     */
    public function getExecutedCount()
    {
        return count($this->executed);
    }

    /**
     * Returns the last executed query, if any.
     *
     * @return array|null
     */
    public function getLastExecuted()
    {
        return $this->getExecutedCount() > 0 ? $this->executed[$this->getExecutedCount() - 1] : null;
    }

    /**
     * Gets the array containing the query meant to be executed at the specified offset (starting at 0).
     *
     * @param int $offset If left at null, will return all of them.
     * @return array|null
     */
    public function getExecuted($offset = null)
    {
        if (is_null($offset))
        {
            return $this->executed;
        }

        return $offset < $this->getExecutedCount() ? $this->executed[$offset] : null;
    }

    /**
     * Sets whether to output queries.
     *
     * @param bool $debug
     */
    public function setDebug($debug)
    {
        $this->debug = !empty($debug);
    }

    /**
     * Returns the current debug status.
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * "Executes" a query.
     *
     * @param array $options
     * @throws \Exception
     * @return QueryMockerResult
     * @throws \Queryer\Exception\QueryMockerResultException Thrown when there are not enough results to satisfy the
     *                                                       the execution request.
     */
    public function execute($options)
    {
        // Save it for later!
        $this->executed[] = $options;

        // This will contain our result options.
        $result = null;

        // Where are we getting our results from?
        if (!is_null($this->result))
        {
            $result = $this->result;
        }
        else if(!is_null($this->results))
        {
            // Make sure we can serve this result.
            if ($this->resultIndex >= count($this->results))
            {
                throw new QueryMockerResultException(
                    'Not expecting any more query executions. There are not enough results specified.',
                    QueryMockerResultException::NOT_ENOUGH_RESULTS
                );
            }

            $result = $this->results[$this->resultIndex++];
        }

        // Are we debugging?
        if ($this->getDebug())
        {
            print_r(array(
                'executed' => $options,
                'result' => $result,
            ));
        }

        return new QueryMockerResult(
            $result['result'],
            $result['affectedRows'],
            $result['insertId'],
            $result['errorCode'],
            $result['errorMessage'],
            $options
        );
    }
}
