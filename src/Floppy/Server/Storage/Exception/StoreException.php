<?php

namespace Floppy\Server\Storage\Exception;

use Exception;
use Floppy\Common\ErrorCodes;
use Floppy\Common\Exception\StorageError;

class StoreException extends Exception implements StorageError
{
    public function __construct($message = null, \Exception $previous = null)
    {
        parent::__construct($message, ErrorCodes::STORE_ERROR, $previous);
    }
}