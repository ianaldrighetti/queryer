<?php
namespace Queryer\Mock;

/**
 * Interface QueryMockerInterface
 *
 * By implementing the QueryMockerInterface anyone can set a mock in the Query builder to have an inside view of what's
 * going on. More details about the methods that are required to be mocked out are below.
 *
 * Please note that the mocker won't be attached to already created Query builders, only those created after setting the
 * mocker.
 *
 * @package Queryer\Mock
 */
interface QueryMockerInterface
{
    /**
     * When the execute method of a Query builder is invoked, this method is called (instead of the database) with the
     * array of options the Query builder has collected up to that point. This method is expected to return an instance
     * of a QueryMockerResult.
     *
     * @param array $options
     * @return \Queryer\Mock\QueryMockerResult
     * @see \Queryer\Mock\MockDriverResult, \Queryer\Mock\QueryMocker
     */
    public function execute($options);
}
