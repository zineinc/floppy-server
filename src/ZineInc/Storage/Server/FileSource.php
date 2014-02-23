<?php

namespace ZineInc\Storage\Server;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use ZineInc\Storage\Server\Stream\InputStream;
use ZineInc\Storage\Server\Stream\StringInputStream;

final class FileSource
{
    private $stream;
    private $fileType;

    /**
     * @param File $file
     *
     * @return FileSource
     */
    public static function fromFile(File $file)
    {
        $content = file_get_contents($file->getPathname());
        $extension = $file instanceof UploadedFile ? $file->getClientOriginalExtension() : $file->getExtension();

        return new self(new StringInputStream($content), new FileType($file->getMimeType(), $extension));
    }

    public function __construct(InputStream $stream, FileType $fileType)
    {
        $this->stream = $stream;
        $this->fileType = $fileType;
    }

    /**
     * @return FileType
     */
    public function fileType()
    {
        return $this->fileType;
    }

    /**
     * @return string
     */
    public function content()
    {
        return $this->stream->read();
    }

    public function discard()
    {
        $this->stream->close();
    }
}