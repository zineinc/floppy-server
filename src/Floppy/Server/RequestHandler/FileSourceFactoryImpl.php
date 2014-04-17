<?php

namespace Floppy\Server\RequestHandler;

use Floppy\Server\RequestHandler\Exception\FileSourceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Floppy\Common\FileSource;
use Floppy\Common\FileType;
use Floppy\Common\Stream\StringInputStream;

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