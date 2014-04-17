<?php


namespace Floppy\Server\RequestHandler\Action\Exception;

use Exception;
use Floppy\Common\StorageException;

class ActionNotFoundException extends \Exception implements StorageException
{
}