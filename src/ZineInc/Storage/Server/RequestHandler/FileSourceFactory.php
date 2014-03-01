<?php

namespace ZineInc\Storage\Server\RequestHandler;

use Symfony\Component\HttpFoundation\Request;
use ZineInc\Storage\Common\FileSource;

interface FileSourceFactory
{
    /**
     * @return \ZineInc\Storage\Common\FileSource
     *
     * @throws FileSourceNotFoundException
     */
    public function createFileSource(Request $request);
}