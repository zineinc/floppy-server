<?php

namespace ZineInc\Storage\Server\Storage;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use ZineInc\Storage\Common\FileId;
use ZineInc\Storage\Server\FileSource;

class FilesystemStorage implements Storage
{
    private $storageDir;
    private $idFactory;
    private $filepathChoosingStrategy;
    private $filesystem;

    public function __construct($storageDir, FilepathChoosingStrategy $filepathChoosingStrategy, IdFactory $idFactory)
    {
        $this->storageDir = rtrim((string)$storageDir, '/');
        $this->filepathChoosingStrategy = $filepathChoosingStrategy;
        $this->idFactory = $idFactory;
    }

    /**
     * @return Filesystem
     */
    private function getFilesystem()
    {
        if($this->filesystem === null) {
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
        //TODO: impl
    }

    public function store(FileSource $fileSource)
    {
        $id = $this->idFactory->id($fileSource);

        $filepath = $this->filepathChoosingStrategy->filepath(new FileId($id));

        $fullFilepath = $this->storageDir.'/'.$filepath;

        try
        {
            $this->getFilesystem()->dumpFile($fullFilepath, $fileSource->content());
        }
        catch(IOException $e)
        {
            throw new StoreException('Error while file storing', $e);
        }

        return $id;
    }

    public function storeVariant(FileSource $file, FileId $fileId)
    {
        //TODO: impl
    }
}