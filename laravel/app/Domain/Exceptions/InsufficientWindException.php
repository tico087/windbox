<?php

namespace App\Domain\Exceptions;

use Exception;

class InsufficientWindException extends Exception
{
    protected $code = 400;
}

class WindPacketNotFoundException extends Exception
{
    protected $code = 404;
}
