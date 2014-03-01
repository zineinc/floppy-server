<?php


namespace ZineInc\Storage\Server\RequestHandler;

use Symfony\Component\HttpFoundation\Response;
use ZineInc\Storage\Common\FileId;
use ZineInc\Storage\Common\FileSource;

class DownloadResponseFactoryImpl implements DownloadResponseFactory
{
    public function createResponse(FileSource $fileSource)
    {
        return new Response($fileSource->content(), 200, array(
            'Content-Type' => $fileSource->fileType()->mimeType(),
        ));
    }
}