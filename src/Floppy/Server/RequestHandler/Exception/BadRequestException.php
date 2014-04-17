<?php


namespace Floppy\Server\RequestHandler\Exception;


use Exception;
use Floppy\Common\ErrorCodes;
use Floppy\Common\Exception\StorageException;

class BadRequestException extends \Exception implements StorageException
{
    public function __construct($message = "", $code = ErrorCodes::BAD_REQUEST, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
} 