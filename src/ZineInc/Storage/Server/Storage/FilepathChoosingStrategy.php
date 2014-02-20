<?php

namespace ZineInc\Storage\Server\Storage;

use ZineInc\Storage\Common\FileId;

//TODO: move to ZineInc\Storage\Common
interface FilepathChoosingStrategy
{
    /**
     * @param \ZineInc\Storage\Common\FileId $fileId
     * @return string Filepath for $fileId. It is relative path to Storage root path
     */
    public function filepath(FileId $fileId);
}