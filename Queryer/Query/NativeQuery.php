<?php
namespace Queryer\Query;
use Queryer\Exception\QueryException;

/**
 * Class NativeQuery
 * @package Queryer\Query
 */
class NativeQuery extends QueryType
{
    /**
     * The engine name identifier (i.e. MySQL, SQLite, etc.).
     * @var string
     */
    private $using;

    /**
     * Sets up the native query builder.
     */
    public function __construct()
    {
        parent::__construct('NATIVE');

        $this->using = null;
    }

    /**
     * Sets the engine the proceeding query is intended for. This method intends for a query method invocation
     * afterwards, i.e.
     * <code>
     *      Query::native()
     *          ->using('mysql')
     *          ->query('SELECT [against MySQL]')
     *          ->using('sqlite')
     *          ->query('SELECT [against SQLite]')
     *          ->execute();
     * </code>
     * This would result in the right query being executed against the database engine being used. So if SQLite was used
     * "SELECT [against SQLite]" would be executed and "SELECT [against MySQL]" would be ignored. Variables can also be
     * used as well.
     *
     * @param string $engineName The engine name, such as MySQL, SQLite, etc.
     * @return $this
     */
    public function using($engineName)
    {
        $this->using = $engineName;

        return $this;
    }

    /**
     * Sets the query for the specified engine type. This is a native query and will be ran against the database as is
     * -- almost. It will parse variables as usual, however. Calling this method consecutively with the same using
     * will just cause the previous query to be overwritten, it will not result in multiple queries being executed.
     *
     * @param string $query The native query to run against the specified engine with the using method.
     * @throws \Queryer\Exception\QueryException If the using method has not been called then this exception will be
     *                                           thrown.
     * @see using
     * @return $this
     */
    public function query($query)
    {
        if (is_null($this->using))
        {
            throw new QueryException(
                "The engine type for the query must be specified with the using method first."
            );
        }

        $this->setOption(strtolower($this->using). '_query', $query);

        return $this;
    }
} 