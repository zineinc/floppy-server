<?php


namespace Floppy\Server\RequestHandler\Action\Exception;


use Exception;
use Floppy\Common\ErrorCodes;
use Floppy\Common\StorageException;

class ActionNotFoundException extends \Exception implements StorageException
{
    public function __construct($message = '', $code = ErrorCodes::FILE_NOT_FOUND, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

} 