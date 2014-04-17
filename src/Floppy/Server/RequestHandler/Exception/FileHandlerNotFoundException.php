<?php

namespace Floppy\Server\RequestHandler\Exception;

use Exception;
use Floppy\Common\ErrorCodes;
use Floppy\Common\Exception\StorageException;

class FileHandlerNotFoundException extends Exception implements StorageException
{
    public function __construct($message = null, $code = ErrorCodes::UNSUPPORTED_FILE_TYPE, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}