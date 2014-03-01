<?php

namespace ZineInc\Storage\Server\Storage;

use ZineInc\Storage\Common\FileSource;

interface IdFactory
{
    public function id(FileSource $fileSource);
}