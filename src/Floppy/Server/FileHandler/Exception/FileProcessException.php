<?php

namespace Floppy\Server\FileHandler\Exception;

use Exception;
use Floppy\Common\Exception\StorageError;

class FileProcessException extends Exception implements StorageError
{
}