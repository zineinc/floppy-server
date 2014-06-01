<?php


namespace Floppy\Server\FileHandler;

use Symfony\Component\HttpFoundation\Response;
use Floppy\Common\FileId;
use Floppy\Common\FileSource;

class DispositionResponseFilter implements ResponseFilter
{
    public function filterResponse(Response $response, FileSource $fileSource, FileId $fileId)
    {
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition('attachment', $fileId->attributes()->get('name') . '.' . $fileSource->fileType()->extension()));
    }
}