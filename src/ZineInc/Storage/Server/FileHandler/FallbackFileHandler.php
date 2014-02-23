<?php

namespace ZineInc\Storage\Server\FileHandler;

use Symfony\Component\HttpFoundation\Response;
use ZineInc\Storage\Common\FileHandler\PathMatcher;
use ZineInc\Storage\Common\FileId;
use ZineInc\Storage\Server\FileSource;

class FallbackFileHandler extends AbstractFileHandler
{
    const TYPE = 'f';

    private $supportedMimeTypes;
    private $supportedExtensions;

    public function __construct(PathMatcher $pathMatcher, array $supportedMimeTypes, array $supportedExtensions)
    {
        parent::__construct($pathMatcher);

        $this->supportedMimeTypes = $supportedMimeTypes;
        $this->supportedExtensions = $supportedExtensions;
    }

    protected function supportedMimeTypes()
    {
        return $this->supportedMimeTypes;
    }

    protected function filterResponse(Response $response, FileSource $fileSource, FileId $fileId)
    {
        $response->headers->makeDisposition('attachment', $fileId->attributes()->get('name') . '.' . $fileSource->fileType()->prefferedExtension());
        return $response;
    }

    protected function supportedExtensions()
    {
        return $this->supportedExtensions;
    }
}