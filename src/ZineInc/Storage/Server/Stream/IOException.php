<?php

namespace ZineInc\Storage\Server\Stream;

use Exception;
use ZineInc\Storage\Common\ErrorCodes;
use ZineInc\Storage\Common\StorageException;

class IOException extends Exception implements StorageException
{
    public function __construct($message = null, Exception $previous = null)
    {
        parent::__construct($message, ErrorCodes::IO, $previous);
    }
}