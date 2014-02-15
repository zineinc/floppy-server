<?php

namespace ZineInc\Storage\Server\Storage;

use ZineInc\Storage\Server\FileSource;

interface IdFactory
{
    public function id(FileSource $fileSource);
}