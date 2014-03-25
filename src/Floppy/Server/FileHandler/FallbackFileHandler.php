<?php

namespace Floppy\Server\FileHandler;

use Symfony\Component\HttpFoundation\Response;
use Floppy\Common\FileHandler\PathMatcher;
use Floppy\Common\FileId;
use Floppy\Common\FileSource;

class FallbackFileHandler extends AbstractFileHandler
{
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