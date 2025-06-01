<?php

namespace WindBox\Domain\Exceptions;

use Exception;

class InsufficientWindException extends Exception
{
    protected $code = 400; // Bad Request
}