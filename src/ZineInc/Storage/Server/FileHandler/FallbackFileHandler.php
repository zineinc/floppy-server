<?php

namespace ZineInc\Storage\Server\FileHandler;

class FallbackFileHandler extends AbstractFileHandler
{
    const TYPE = 'f';

    private $supportedMimeTypes;

    public function __construct(VariantMatcher $variantMatcher, array $supportedMimeTypes)
    {
        parent::__construct($variantMatcher);

        $this->supportedMimeTypes = $supportedMimeTypes;
    }

    protected function supportedMimeTypes()
    {
        return $this->supportedMimeTypes;
    }
}