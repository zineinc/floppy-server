<?php

namespace ZineInc\Storage\Server\FileHandler;

use ZineInc\Storage\Common\FileHandler\PathMatcher;

class FallbackFileHandler extends AbstractFileHandler
{
    const TYPE = 'f';

    private $supportedMimeTypes;

    public function __construct(PathMatcher $variantMatcher, array $supportedMimeTypes)
    {
        parent::__construct($variantMatcher);

        $this->supportedMimeTypes = $supportedMimeTypes;
    }

    protected function supportedMimeTypes()
    {
        return $this->supportedMimeTypes;
    }
}