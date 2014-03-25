<?php

namespace Floppy\Server\FileHandler;

use Imagine\Image\ImagineInterface;
use Floppy\Common\AttributesBag;
use Floppy\Common\FileSource;

interface ImageProcess
{
    public function process(ImagineInterface $imagine, FileSource $fileSource, AttributesBag $attrs);
}