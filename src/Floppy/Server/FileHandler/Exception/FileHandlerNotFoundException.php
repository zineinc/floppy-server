<?php

namespace Floppy\Server\FileHandler\Exception;

use Exception;
use Floppy\Common\Exception\StorageException;

class FileHandlerNotFoundException extends Exception implements StorageException
{
}