<?php

namespace ZineInc\Storage\Common\FileHandler;

use ZineInc\Storage\Common\FileId;

interface PathMatcher
{
    /**
     * @return FileId
     * @throws PathMatchingException
     */
    public function match($variantFilepath);
}