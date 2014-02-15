<?php

namespace ZineInc\Storage\Server\Storage;

use Symfony\Component\Filesystem\Filesystem;
use ZineInc\Storage\Server\FileId;
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
        $this->filesystem = new Filesystem();
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

        $fileSource->stream()->resetInput();

        //TODO: exception support
        $this->filesystem->dumpFile($fullFilepath, $fileSource->stream()->read());

        return $id;
    }

    public function storeVariant(FileSource $file, FileId $fileId)
    {
        //TODO: impl
    }
}