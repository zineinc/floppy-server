<?php

namespace Floppy\Server\Storage;

use Floppy\Common\FileSource;

interface IdFactory
{
    public function id(FileSource $fileSource);
}