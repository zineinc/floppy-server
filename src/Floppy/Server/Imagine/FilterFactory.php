<?php

namespace Floppy\Server\Imagine;

use Imagine\Filter\FilterInterface;

interface FilterFactory
{
    /**
     * @param $name
     * @param array $options
     * @return FilterInterface
     */
    public function createFilter($name, array $options = array());
}