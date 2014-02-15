<?php

namespace ZineInc\Storage\Server\Storage;

use Exception;
use ZineInc\Storage\Server\FileId;
use ZineInc\Storage\Server\FileSource;

interface Storage
{
    /**
     * Stores file to persistent storage
     *
     * @return string file id
     */
    public function store(FileSource $file);

    /**
     * Stores file variant to persistent storage
     */
    public function storeVariant(FileSource $file, FileId $fileId);

    /**
     * Returns file source for given $fileId
     *
     * @return FileSource
     *
     * @throws Exception When file not found
     */
    public function getSource(FileId $fileId);
}