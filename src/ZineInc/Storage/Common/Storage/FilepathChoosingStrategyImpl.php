<?php

namespace ZineInc\Storage\Common\Storage;

use InvalidArgumentException;
use ZineInc\Storage\Common\FileId;

class FilepathChoosingStrategyImpl implements FilepathChoosingStrategy
{
    private $charsForDir;
    private $dirCount;
    private $variantRootDir;
    private $origRootDir;

    public function __construct($dirCount = 2, $charsForDir = 3, $origRootDir = 'orig', $variantRootDir = 'v')
    {
        if ($dirCount < 1 || $charsForDir < 1) {
            throw new InvalidArgumentException(sprintf('$dirCount and $charsForDir have to be integer >= 1, given: (%s, %s)', $dirCount, $charsForDir));
        }

        $this->charsForDir = (int)$charsForDir;
        $this->dirCount = (int)$dirCount;
        $this->origRootDir = (string)$origRootDir;
        $this->variantRootDir = (string)$variantRootDir;
    }

    public function filepath(FileId $fileId)
    {
        $id = $fileId->id();

        $parts = array();

        $parts[] = $fileId->isVariant() ? $this->variantRootDir : $this->origRootDir;

        for ($i = 0; $i < $this->dirCount; $i++) {
            $parts[] = substr($id, $i * $this->charsForDir, $this->charsForDir);
        }

        return implode('/', $parts);
    }
}