<?php


namespace ZineInc\Storage\Server\RequestHandler;

use ZineInc\Storage\Common\FileId;
use ZineInc\Storage\Common\FileSource;

interface DownloadResponseFactory
{
    public function createResponse(FileSource $fileSource);
} 