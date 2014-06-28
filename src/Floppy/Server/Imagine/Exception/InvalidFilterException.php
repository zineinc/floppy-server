<?php


namespace Floppy\Server\Imagine\Exception;


use Floppy\Common\Exception\StorageError;

class InvalidFilterException extends \InvalidArgumentException implements FilterError
{
}