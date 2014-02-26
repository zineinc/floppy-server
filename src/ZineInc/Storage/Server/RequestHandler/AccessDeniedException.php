<?php


namespace ZineInc\Storage\Server\RequestHandler;


use Exception;
use ZineInc\Storage\Common\ErrorCodes;
use ZineInc\Storage\Common\StorageException;

class AccessDeniedException extends \Exception implements StorageException
{
    public function __construct($message = "", $code = ErrorCodes::ACCESS_DENIED, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}