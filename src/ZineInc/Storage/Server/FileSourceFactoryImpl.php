<?php

namespace ZineInc\Storage\Server;

use ZineInc\Storage\Server\Stream\StringStream;
use Symfony\Component\HttpFoundation\Request;

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

        return new FileSource(new StringStream($content), new FileType($file->getMimeType(), $file->guessExtension()));
    }
}