<?php

namespace Floppy\Server\RequestHandler\Exception;

use Exception;
use Floppy\Common\ErrorCodes;
use Floppy\Common\Exception\StorageException;

class FileSourceNotFoundException extends Exception implements StorageException
{
    public function __construct($message = null, Exception $previous = null)
    {
        parent::__construct($message, ErrorCodes::FILE_NOT_FOUND, $previous);
    }
}