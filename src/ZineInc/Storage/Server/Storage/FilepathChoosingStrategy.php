<?php

namespace ZineInc\Storage\Server\Storage;

use ZineInc\Storage\Server\FileId;

interface FilepathChoosingStrategy
{
    /**
     * @param \ZineInc\Storage\Server\FileId $fileId
     * @return string Filepath for $fileId. It is relative path to Storage root path
     */
    public function filepath(FileId $fileId);
}