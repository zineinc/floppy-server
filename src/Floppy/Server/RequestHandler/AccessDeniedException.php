<?php


namespace Floppy\Server\RequestHandler;


use Exception;
use Floppy\Common\ErrorCodes;
use Floppy\Common\StorageException;

class AccessDeniedException extends \Exception implements StorageException
{
    public function __construct($message = "", $code = ErrorCodes::ACCESS_DENIED, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}