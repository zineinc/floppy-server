<?php

namespace ZineInc\Storage\Server;

use Exception;

class FileHandlerNotFoundException extends Exception implements StorageException
{
    public function __construct($message = null, Exception $previous = null)
    {
        parent::__construct($message, ErrorCodes::UNSUPPORTED_FILE_TYPE, $previous);
    }
}