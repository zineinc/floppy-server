<?php

namespace ZineInc\Storage\Server\FileHandler;

use Symfony\Component\HttpFoundation\Response;
use ZineInc\Storage\Common\FileHandler\PathMatcher;
use ZineInc\Storage\Common\FileId;
use ZineInc\Storage\Common\FileSource;

class FallbackFileHandler extends AbstractFileHandler
{
    const TYPE = 'f';

    private $supportedMimeTypes;
    private $supportedExtensions;

    public function __construct(PathMatcher $pathMatcher, array $supportedMimeTypes, array $supportedExtensions, array $responseFilters = array())
    {
        parent::__construct($pathMatcher, $responseFilters);

        $this->supportedMimeTypes = $supportedMimeTypes;
        $this->supportedExtensions = $supportedExtensions;
    }

    protected function supportedMimeTypes()
    {
        return $this->supportedMimeTypes;
    }

    protected function supportedExtensions()
    {
        return $this->supportedExtensions;
    }
}