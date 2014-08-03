<?php

namespace Floppy\Server\FileHandler;

use Floppy\Server\FileHandler\Exception\FileProcessException;
use Imagine\Image\ImagineInterface;
use Floppy\Common\AttributesBag;
use Floppy\Common\FileSource;

interface ImageProcess
{
    /**
     * @param ImagineInterface $imagine
     * @param FileSource $fileSource
     * @param AttributesBag $attrs
     *
     * @return FileSource
     *
     * @throws FileProcessException
     */
    public function process(ImagineInterface $imagine, FileSource $fileSource, AttributesBag $attrs);
}