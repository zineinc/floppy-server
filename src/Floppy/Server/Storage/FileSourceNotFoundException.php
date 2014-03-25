<?php

namespace Floppy\Server\Storage;


use Floppy\Common\ErrorCodes;
use Floppy\Common\StorageError;

class FileSourceNotFoundException extends \Exception implements StorageError
{
    public function __construct($message = null, \Exception $previous = null)
    {
        parent::__construct($message, ErrorCodes::FILE_NOT_FOUND, $previous);
    }
} 