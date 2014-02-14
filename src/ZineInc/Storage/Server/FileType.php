<?php

namespace ZineInc\Storage\Server;

final class FileType
{
    private $mimeType;
    private $prefferedExtension;

    public function __construct($mimeType, $prefferedExtension)
    {
        $this->mimeType = (string) $mimeType;
        $this->prefferedExtension = (string) $prefferedExtension;
    }

    public function mimeType()
    {
        return $this->mimeType;
    }

    public function prefferedExtension()
    {
        return $this->prefferedExtension;
    }
}