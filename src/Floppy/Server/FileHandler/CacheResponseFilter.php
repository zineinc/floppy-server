<?php


namespace Floppy\Server\FileHandler;

use Floppy\Common\FileId;
use Floppy\Common\FileSource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheResponseFilter implements ResponseFilter
{
    private $cacheMaxAge;
    private $useEtag;

    public function __construct($cacheMaxAge, $useEtag)
    {
        $this->cacheMaxAge = (int) $cacheMaxAge;
        $this->useEtag = (boolean) $useEtag;
    }

    public function filterResponse(Response $response, FileSource $fileSource, FileId $fileId)
    {
        $response->setCache(array(
            'max_age' => $this->cacheMaxAge,
            'public' => true,
            'etag' => $this->useEtag ? md5($fileId->id().'|'.serialize($fileId->attributes())) : null,
        ));
    }
}