<?php

namespace ZineInc\Storage\Server\Storage;


use ZineInc\Storage\Common\ErrorCodes;
use ZineInc\Storage\Common\StorageError;

class FileSourceNotFoundException extends \Exception implements StorageError {
    public function __construct($message = null, \Exception $previous = null)
    {
        parent::__construct($message, ErrorCodes::FILE_NOT_FOUND, $previous);
    }
} 