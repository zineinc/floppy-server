<?php

namespace ZineInc\Storage\Server;

interface FileSource
{
    public function getFileType();

    /**
     * @return OutputStream|null Null when FileSource is only readable
     */
    public function getOutput();

    /**
     * @return InputStream
     */
    public function getInput();
}