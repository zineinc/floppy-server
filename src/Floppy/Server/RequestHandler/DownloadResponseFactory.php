<?php


namespace Floppy\Server\RequestHandler;

use Floppy\Common\FileId;
use Floppy\Common\FileSource;

interface DownloadResponseFactory
{
    public function createResponse(FileSource $fileSource);
} 