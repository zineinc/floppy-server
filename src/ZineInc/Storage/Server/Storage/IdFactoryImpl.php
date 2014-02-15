<?php

namespace ZineInc\Storage\Server\Storage;

use ZineInc\Storage\Server\FileSource;

class IdFactoryImpl implements IdFactory
{
    private $alg;

    public function __construct($alg = 'md5')
    {
        $this->alg = (string) $alg;
    }

    public function id(FileSource $fileSource)
    {
        $fileSource->stream()->resetInput();
        $content = $fileSource->stream()->read();

        return hash($this->alg, $content).'.'.$fileSource->fileType()->prefferedExtension();
    }
}