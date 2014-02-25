<?php


namespace ZineInc\Storage\Server\FileHandler;

use Symfony\Component\HttpFoundation\Response;
use ZineInc\Storage\Common\FileId;
use ZineInc\Storage\Server\FileSource;

interface ResponseFilter
{
    /**
     * @param Response $response Response to filter. It can be modified by filter.
     * @param FileSource $fileSource
     * @param FileId $fileId
     *
     * @return void
     */
    public function filterResponse(Response $response, FileSource $fileSource, FileId $fileId);
} 