<?php

namespace Floppy\Server\Storage\Exception;

use Exception;
use Floppy\Common\Exception\StorageError;

class FileSourceNotFoundException extends \Exception implements StorageError
{
}