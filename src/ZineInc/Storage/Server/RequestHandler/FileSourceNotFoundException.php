<?php

namespace ZineInc\Storage\Server\RequestHandler;

use Exception;
use ZineInc\Storage\Common\ErrorCodes;
use ZineInc\Storage\Common\StorageException;

class FileSourceNotFoundException extends Exception implements StorageException
{
    public function __construct($message = null, Exception $previous = null)
    {
        parent::__construct($message, ErrorCodes::FILE_NOT_FOUND, $previous);
    }
}