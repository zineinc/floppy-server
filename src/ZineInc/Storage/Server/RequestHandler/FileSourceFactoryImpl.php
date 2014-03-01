<?php

namespace ZineInc\Storage\Server\RequestHandler;

use Symfony\Component\HttpFoundation\Request;
use ZineInc\Storage\Common\FileSource;
use ZineInc\Storage\Common\FileType;
use ZineInc\Storage\Common\Stream\StringInputStream;

class FileSourceFactoryImpl implements FileSourceFactory
{
    const FILE_KEY = 'file';

    private $fileKey;

    public function __construct($fileKey = self::FILE_KEY)
    {
        $this->fileKey = $fileKey;
    }

    public function createFileSource(Request $request)
    {
        if (!$request->files->has($this->fileKey)) {
            throw new FileSourceNotFoundException();
        }

        $file = $request->files->get($this->fileKey);

        return FileSource::fromFile($file);
    }
}