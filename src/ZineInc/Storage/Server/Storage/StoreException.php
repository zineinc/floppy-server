<?php

namespace ZineInc\Storage\Server\Storage;

use Exception;
use ZineInc\Storage\Server\ErrorCodes;
use ZineInc\Storage\Server\StorageError;

class StoreException extends Exception implements StorageError
{
    public function __construct($message = null, \Exception $previous = null)
    {
        parent::__construct($message, ErrorCodes::STORE_ERROR, $previous);
    }
}