<?php


namespace Floppy\Server\RequestHandler\Exception;


use Exception;
use Floppy\Common\Exception\StorageException;

class AccessDeniedException extends \Exception implements StorageException
{
}