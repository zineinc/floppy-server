<?php


namespace Floppy\Server\RequestHandler\Exception;


use Exception;
use Floppy\Common\Exception\StorageException;

class BadRequestException extends \Exception implements StorageException
{
} 