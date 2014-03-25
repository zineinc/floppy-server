<?php

namespace Floppy\Server\RequestHandler;

use Symfony\Component\HttpFoundation\Request;
use Floppy\Common\FileSource;

interface FileSourceFactory
{
    /**
     * @return \Floppy\Common\FileSource
     *
     * @throws FileSourceNotFoundException
     */
    public function createFileSource(Request $request);
}