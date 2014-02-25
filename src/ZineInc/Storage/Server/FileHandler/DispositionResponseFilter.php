<?php


namespace ZineInc\Storage\Server\FileHandler;

use Symfony\Component\HttpFoundation\Response;
use ZineInc\Storage\Common\FileId;
use ZineInc\Storage\Server\FileSource;

class DispositionResponseFilter implements ResponseFilter
{
    public function filterResponse(Response $response, FileSource $fileSource, FileId $fileId)
    {
        $response->headers->makeDisposition('attachment', $fileId->attributes()->get('name') . '.' . $fileSource->fileType()->prefferedExtension());
    }
}