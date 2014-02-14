<?php

namespace ZineInc\Storage\Server;

use Symfony\Component\HttpFoundation\Request;

interface FileSourceFactory
{
    /**
     * @return FileSource
     *
     * @throws FileSourceNotFoundException
     */
    public function createFileSource(Request $request);
}