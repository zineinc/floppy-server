<?php

namespace ZineInc\Storage\Server;

use Exception;

class FileSourceNotFoundException extends Exception implements StorageException
{
    public function __construct($message = null, Exception $previous = null)
    {
        parent::__construct($message, ErrorCodes::FILE_NOT_FOUND, $previous);
    }
}