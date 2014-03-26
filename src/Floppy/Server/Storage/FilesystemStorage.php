<?php

namespace Floppy\Server\Storage;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Floppy\Common\FileId;
use Floppy\Common\Storage\FilepathChoosingStrategy;
use Floppy\Common\FileSource;

class FilesystemStorage implements Storage
{
    private $storageDir;
    private $idFactory;
    private $filepathChoosingStrategy;
    private $filesystem;
    private $dirChmod;
    private $fileChmod;

    public function __construct($storageDir, FilepathChoosingStrategy $filepathChoosingStrategy, IdFactory $idFactory, $dirChmod = 0755, $fileChmod = 0644)
    {
        $this->storageDir = rtrim((string)$storageDir, '/');
        $this->filepathChoosingStrategy = $filepathChoosingStrategy;
        $this->idFactory = $idFactory;
        $this->dirChmod = $dirChmod;
        $this->fileChmod = $fileChmod;
    }

    /**
     * @return Filesystem
     */
    private function getFilesystem()
    {
        if ($this->filesystem === null) {
            $this->filesystem = new Filesystem();
        }

        return $this->filesystem;
    }

    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function getSource(FileId $fileId)
    {
        $fullFilepath = $this->getFilepath($fileId);

        try {
            $file = new File($fullFilepath, true);
            return FileSource::fromFile($file);
        } catch (FileNotFoundException $e) {
            throw new FileSourceNotFoundException($e->getMessage(), $e);
        }
    }


    /**
     * @return string
     */
    private function getFilepath(FileId $fileId)
    {
        $filepath = $this->filepathChoosingStrategy->filepath($fileId);
        $fullFilepath = $this->storageDir . '/' . $filepath . '/' . $fileId->filename();

        return $fullFilepath;
    }

    public function store(FileSource $fileSource, FileId $fileId = null)
    {
        $fileId = $fileId ? : new FileId($this->idFactory->id($fileSource));

        $this->ensureValidFilepath($fileId->filename());

        $filepath = $this->filepathChoosingStrategy->filepath($fileId) . '/' . $fileId->filename();

        $fullFilepath = $this->storageDir . '/' . $filepath;

        try {
            $this->getFilesystem()->mkdir(dirname($fullFilepath), $this->dirChmod);
            $this->getFilesystem()->dumpFile($fullFilepath, $fileSource->content(), $this->fileChmod);
        } catch (IOException $e) {
            throw new StoreException('Error while file storing', $e);
        }

        return $fileId->id();
    }

    /**
     * @param $filepath
     *
     * @throws StoreException
     */
    private function ensureValidFilepath($filepath)
    {
        if ($filepath !== null && strpos($filepath, '..') !== false) {
            throw new StoreException(sprintf('Invalid filepath: %s', $filepath));
        }
    }

    public function exists(FileId $fileId)
    {
        $filepath = $this->getFilepath($fileId);

        $file = new File($filepath, false);
        return $file->isFile();
    }
}