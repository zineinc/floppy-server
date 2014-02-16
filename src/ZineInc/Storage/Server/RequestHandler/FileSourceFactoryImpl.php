<?php

namespace ZineInc\Storage\Server\RequestHandler;

use Symfony\Component\HttpFoundation\Request;
use ZineInc\Storage\Server\FileSource;
use ZineInc\Storage\Server\FileType;
use ZineInc\Storage\Server\Stream\StringInputStream;

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
        if(!$request->files->has($this->fileKey))
        {
            throw new FileSourceNotFoundException();
        }

        $file = $request->files->get($this->fileKey);

        $content = file_get_contents($file->getPathname());

        return new FileSource(new StringInputStream($content), new FileType($file->getMimeType(), $file->guessExtension()));
    }
}