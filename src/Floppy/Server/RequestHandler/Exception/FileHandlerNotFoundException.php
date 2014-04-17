<?php

namespace Floppy\Server\RequestHandler\Exception;

use Exception;
use Floppy\Common\Exception\StorageException;

class FileHandlerNotFoundException extends Exception implements StorageException
{
}