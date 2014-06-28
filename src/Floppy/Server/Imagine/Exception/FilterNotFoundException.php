<?php


namespace Floppy\Server\Imagine\Exception;

use Exception;
use Floppy\Common\Exception\StorageException;

class FilterNotFoundException extends \InvalidArgumentException implements FilterException
{
    public function __construct($filterName, $code = 0, Exception $previous = null)
    {
        parent::__construct(sprintf('Filter %s not found', $filterName), $code, $previous);
    }
}