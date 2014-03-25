<?php

namespace Floppy\Server\FileHandler;

use Exception;
use Floppy\Common\ErrorCodes;
use Floppy\Common\StorageError;

class FileProcessException extends Exception implements StorageError
{
    public function __construct($message = null, Exception $previous = null)
    {
        parent::__construct($message, ErrorCodes::FILE_PROCESS_ERROR, $previous);
    }
}