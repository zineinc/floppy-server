<?php

namespace ZineInc\Storage\Server\FileHandler;

use Exception;
use ZineInc\Storage\Server\ErrorCodes;
use ZineInc\Storage\Common\StorageError;

class FileProcessException extends Exception implements StorageError
{
    public function __construct($message = null, Exception $previous = null)
    {
        parent::__construct($message, ErrorCodes::FILE_PROCESS_ERROR, $previous);
    }
}