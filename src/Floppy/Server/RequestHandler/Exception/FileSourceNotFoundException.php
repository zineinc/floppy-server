<?php

namespace Floppy\Server\RequestHandler\Exception;

use Exception;
use Floppy\Common\Exception\StorageException;

class FileSourceNotFoundException extends Exception implements StorageException
{
}