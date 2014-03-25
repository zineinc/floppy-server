<?php

namespace Floppy\Server\Storage;

use Exception;
use Floppy\Common\FileId;
use Floppy\Common\FileSource;

interface Storage
{
    /**
     * Stores file to persistent storage
     *
     * @param FileSource $file file to store
     * @param FileId $fileId id of stored file. It will be created by storage when is null
     *
     * @return string file id
     *
     * @throws StoreException
     */
    public function store(FileSource $file, FileId $fileId = null);

    /**
     * Does file with given FileId exist
     *
     * @param FileId $fileId id of file to check
     *
     * @return boolean
     */
    public function exists(FileId $fileId);

    /**
     * Returns file source for given $fileId
     *
     * @param FileId $fileId
     *
     * @return FileSource
     *
     * @throws FileSourceNotFoundException When file not found
     */
    public function getSource(FileId $fileId);
}