<?php

namespace ZineInc\Storage\Server\FileHandler;

class FallbackFileHandler extends AbstractFileHandler
{
    const TYPE = 'f';

    private $supportedMimeTypes;

    public function __construct(array $supportedMimeTypes)
    {
        $this->supportedMimeTypes = $supportedMimeTypes;
    }

    protected function supportedMimeTypes()
    {
        return $this->supportedMimeTypes;
    }
}