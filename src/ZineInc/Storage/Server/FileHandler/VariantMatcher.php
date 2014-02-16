<?php

namespace ZineInc\Storage\Server\FileHandler;

use ZineInc\Storage\Server\FileId;

interface VariantMatcher
{
    /**
     * @return FileId
     * @throws VariantMatchingException
     */
    public function match($variantFilepath);
}