<?php

namespace ZineInc\Storage\Server\Storage;

use Exception;
use ZineInc\Storage\Common\FileId;
use ZineInc\Storage\Server\FileSource;

interface Storage
{
    /**
     * Stores file to persistent storage
     *
     * @return string file id
     *
     * @throws StoreException
     */
    public function store(FileSource $file, FileId $fileId = null, $filename = null);

    /**
     * Returns file source for given $fileId
     *
     * @return FileSource
     *
     * @throws FileSourceNotFoundException When file not found
     */
    public function getSource(FileId $fileId);
}