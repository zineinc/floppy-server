<?php

namespace ZineInc\Storage\Server;

use ZineInc\Storage\Server\Stream\InputStream;

final class FileSource
{
    private $stream;
    private $fileType;

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