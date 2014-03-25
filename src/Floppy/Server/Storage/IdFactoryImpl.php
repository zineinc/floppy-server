<?php

namespace Floppy\Server\Storage;

use Floppy\Common\FileSource;

class IdFactoryImpl implements IdFactory
{
    private $alg;

    public function __construct($alg = 'md5')
    {
        $this->alg = (string)$alg;
    }

    public function id(FileSource $fileSource)
    {
        $content = $fileSource->content();

        return hash($this->alg, $content) . '.' . $fileSource->fileType()->prefferedExtension();
    }
}