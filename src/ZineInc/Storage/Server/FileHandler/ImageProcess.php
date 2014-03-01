<?php

namespace ZineInc\Storage\Server\FileHandler;

use Imagine\Image\ImagineInterface;
use ZineInc\Storage\Common\AttributesBag;
use ZineInc\Storage\Common\FileSource;

interface ImageProcess
{
    public function process(ImagineInterface $imagine, FileSource $fileSource, AttributesBag $attrs);
}