<?php

namespace ZineInc\Storage\Server;

use Symfony\Component\HttpFoundation\Request;

interface RequestConverter
{
    /**
     * Converts request object to FileSource
     *
     * @param Request $request
     * @return FileSource
     *
     * @throws \Exception When there are no file in $request
     */
    public function convertToFileSource(Request $request);
}