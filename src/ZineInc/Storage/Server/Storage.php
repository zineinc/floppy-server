<?php

namespace ZineInc\Storage\Server;

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
     * @throws \Exception When file not found
     */
    public function getSource(FileId $fileId);
}