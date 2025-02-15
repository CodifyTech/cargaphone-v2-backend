<?php

namespace Domains\Auth\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class BaseServiceException extends HttpException
{
    public function __construct($message = null, \Throwable $previous = null, array $headers = [], $code = 0)
    {
        parent::__construct(401, $message, $previous, $headers, $code);
    }
}
