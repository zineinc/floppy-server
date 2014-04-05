<?php

namespace Floppy\Server\FileHandler;

use Exception;
use Floppy\Common\ErrorCodes;
use Floppy\Common\StorageException;

class FileHandlerNotFoundException extends Exception implements StorageException
{
    public function __construct($message = null, $code = ErrorCodes::UNSUPPORTED_FILE_TYPE, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}