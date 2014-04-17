<?php


namespace Floppy\Server\RequestHandler\Exception;


use Exception;
use Floppy\Common\ErrorCodes;
use Floppy\Common\Exception\StorageException;

class AccessDeniedException extends \Exception implements StorageException
{
    public function __construct($message = "", $code = ErrorCodes::ACCESS_DENIED, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}