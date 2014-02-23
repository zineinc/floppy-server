<?php

namespace ZineInc\Storage\Common\FileHandler;

use ZineInc\Storage\Common\FileId;

interface PathMatcher
{
    /**
     * @return FileId
     * @throws PathMatchingException When data from filepath is invalid (checksum is invalid etc.)
     */
    public function match($variantFilepath);

    /**
     * @param $variantFilepath
     *
     * @return boolean
     */
    public function matches($variantFilepath);
}