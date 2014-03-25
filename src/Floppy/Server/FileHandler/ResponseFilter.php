<?php


namespace Floppy\Server\FileHandler;

use Symfony\Component\HttpFoundation\Response;
use Floppy\Common\FileId;
use Floppy\Common\FileSource;

interface ResponseFilter
{
    /**
     * @param Response $response Response to filter. It can be modified by filter.
     * @param \Floppy\Common\FileSource $fileSource
     * @param FileId $fileId
     *
     * @return void
     */
    public function filterResponse(Response $response, FileSource $fileSource, FileId $fileId);
} 