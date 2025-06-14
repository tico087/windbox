<?php

namespace WindBox\Domain\Exceptions;

use Exception;

class WindPacketNotFoundException extends Exception
{
    protected $code = 404; 
}