<?php


namespace Floppy\Server\RequestHandler\Security;


use Floppy\Common\FileId;
use Floppy\Common\FileSource;
use Floppy\Common\HasFileInfo;
use Symfony\Component\HttpFoundation\Request;

interface Rule
{
    /**
     * @return HasFileInfo
     *
     * @throw Floppy\Common\StorageException
     */
    public function processRule(Request $request, HasFileInfo $object);
} 