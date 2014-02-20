<?php

namespace ZineInc\Storage\Common\FileHandler;

use ZineInc\Storage\Common\FileId;

interface VariantMatcher
{
    /**
     * @return FileId
     * @throws VariantMatchingException
     */
    public function match($variantFilepath);
}