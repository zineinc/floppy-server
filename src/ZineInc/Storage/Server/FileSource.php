<?php

namespace ZineInc\Storage\Server;

interface FileSource
{
    public function getFileType();

    /**
     * @return Stream
     */
    public function getStream();
}