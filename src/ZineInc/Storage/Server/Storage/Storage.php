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
     * @param FileSource $file file to store
     * @param FileId $fileId id of stored file. It will be created by storage when is null
     * @param string $filename filename of file to store. It will be $fileId->id() when $filename is null
     *
     * @return string file id
     *
     * @throws StoreException
     */
    public function store(FileSource $file, FileId $fileId = null, $filename = null);

    /**
     * Does file with given FileId exist
     *
     * @param FileId $fileId id of file to check
     * @param string $filename filename of file to check. It will be $fileId->id() when $filename is null
     *
     * @return boolean
     */
    public function exists(FileId $fileId, $filename = null);

    /**
     * Returns file source for given $fileId
     *
     * @param FileId $fileId
     * @param string $filename
     *
     * @return FileSource
     *
     * @throws FileSourceNotFoundException When file not found
     */
    public function getSource(FileId $fileId, $filename = null);
}