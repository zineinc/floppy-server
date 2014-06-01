<?php

namespace Floppy\Server\Storage;

use Floppy\Common\FileSource;

class IdFactoryImpl implements IdFactory
{
    private $alg;
    private $salt;

    public function __construct($salt = '', $alg = 'md5')
    {
        $this->alg = (string)$alg;
        $this->salt = (string)$salt;
    }

    public function id(FileSource $fileSource)
    {
        $content = $fileSource->content();

        return hash($this->alg, $content.$this->salt) . '.' . $fileSource->fileType()->prefferedExtension();
    }
}