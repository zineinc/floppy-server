<?php


namespace Floppy\Server\Storage;

use Floppy\Common\FileId;
use Floppy\Common\FileSource;
use Floppy\Common\HasFileInfo;

class AccessSupportStorage implements Storage
{
    private $privateStorage;
    private $publicStorage;

    public function __construct(Storage $publicStorage, Storage $privateStorage)
    {
        $this->privateStorage = $privateStorage;
        $this->publicStorage = $publicStorage;
    }

    public function store(FileSource $file, FileId $fileId = null)
    {
        return $this->chooseStorage($file)->store($file, $fileId);
    }

    /**
     * @return Storage
     */
    private function chooseStorage(HasFileInfo $fileInfo)
    {
        return $fileInfo->info()->get('access') === 'private' ? $this->privateStorage : $this->publicStorage;
    }

    public function exists(FileId $fileId)
    {
        return $this->chooseStorage($fileId)->exists($fileId);
    }

    public function getSource(FileId $fileId)
    {
        $fileSource = $this->chooseStorage($fileId)->getSource($fileId);

        return $fileSource->withInfo($fileId->info()->all());
    }
}