<?php

namespace Floppy\Server\Storage\Exception;


use Exception;
use Floppy\Common\ErrorCodes;
use Floppy\Common\Exception\StorageError;

class FileSourceNotFoundException extends \Exception implements StorageError
{
    public function __construct($message = null, \Exception $previous = null)
    {
        parent::__construct($message, ErrorCodes::FILE_NOT_FOUND, $previous);
    }
} 