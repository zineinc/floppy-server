<?php

namespace ZineInc\Storage\Server\RequestHandler;

use Symfony\Component\HttpFoundation\Request;
use ZineInc\Storage\Server\FileSource;

interface FileSourceFactory
{
    /**
     * @return FileSource
     *
     * @throws FileSourceNotFoundException
     */
    public function createFileSource(Request $request);
}