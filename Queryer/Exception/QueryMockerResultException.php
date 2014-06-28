<?php

namespace Queryer\Exception;

/**
 * Class QueryMockerResultException
 * @package Queryer\Exception
 */
class QueryMockerResultException extends \Exception
{
    /**
     * Indicates that there are not enough results to return a result.
     */
    const NOT_ENOUGH_RESULTS = 1;
} 