<?php

namespace ZineInc\Storage\Server;

use ZineInc\Storage\Server\Stream\Stream;

final class FileSource
{
    private $stream;
    private $fileType;

    public function __construct(Stream $stream, FileType $fileType)
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
     * @return Stream
     */
    public function stream()
    {
        return $this->stream;
    }
}