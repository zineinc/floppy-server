<?php

namespace ZineInc\Storage\Server\Storage;

use InvalidArgumentException;
use ZineInc\Storage\Common\FileId;

class FilepathChoosingStrategyImpl implements FilepathChoosingStrategy
{
    private $charsForDir;
    private $dirCount;

    public function __construct($dirCount = 2, $charsForDir = 3)
    {
        if($dirCount < 1 || $charsForDir < 1)
        {
            throw new InvalidArgumentException(sprintf('$dirCount and $charsForDir have to be integer >= 1, given: (%s, %s)', $dirCount, $charsForDir));
        }

        $this->charsForDir = (int) $charsForDir;
        $this->dirCount = (int) $dirCount;
    }

    public function filepath(FileId $fileId)
    {
        $id = $fileId->id();

        $parts = array();

        for($i=0; $i<$this->dirCount; $i++)
        {
            $parts[] = substr($id, $i*$this->charsForDir, $this->charsForDir);
        }

        $parts[] = $id;

        return implode('/', $parts);
    }
}