<?php

namespace ZineInc\Storage\Common\Storage;

use ZineInc\Storage\Common\FileId;

interface FilepathChoosingStrategy
{
    /**
     * @param \ZineInc\Storage\Common\FileId $fileId
     *
     * @return string Filepath for $fileId. It is relative path to Storage root path
     */
    public function filepath(FileId $fileId);
}