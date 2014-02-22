<?php

namespace ZineInc\Storage\Server\RequestHandler;

use Exception;
use ZineInc\Storage\Server\ErrorCodes;
use ZineInc\Storage\Server\StorageException;

class FileHandlerNotFoundException extends Exception implements StorageException
{
    public function __construct($message = null, $code = ErrorCodes::UNSUPPORTED_FILE_TYPE, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}