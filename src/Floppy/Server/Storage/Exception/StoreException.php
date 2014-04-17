<?php

namespace Floppy\Server\Storage\Exception;

use Exception;
use Floppy\Common\Exception\StorageError;

class StoreException extends Exception implements StorageError
{
}