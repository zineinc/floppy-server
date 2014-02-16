<?php

namespace ZineInc\Storage\Server\FileHandler;

use ZineInc\Storage\Server\FileId;

interface VariantMatcher
{
    /**
     * @return FileId
     */
    public function match($variantFilepath);
}