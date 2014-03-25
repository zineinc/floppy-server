<?php


namespace Floppy\Server\RequestHandler;

use Symfony\Component\HttpFoundation\Response;
use Floppy\Common\FileId;
use Floppy\Common\FileSource;

class DownloadResponseFactoryImpl implements DownloadResponseFactory
{
    public function createResponse(FileSource $fileSource)
    {
        return new Response($fileSource->content(), 200, array(
            'Content-Type' => $fileSource->fileType()->mimeType(),
        ));
    }
}