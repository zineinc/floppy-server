<?php


namespace Floppy\Server\RequestHandler\Event;


use Floppy\Common\FileId;

interface ETagGenerator
{
    public function generateETag(FileId $fileId);
} 