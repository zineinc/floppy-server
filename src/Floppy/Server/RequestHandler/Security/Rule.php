<?php


namespace Floppy\Server\RequestHandler\Security;


use Floppy\Common\FileId;
use Floppy\Common\FileSource;
use Symfony\Component\HttpFoundation\Request;

interface Rule
{
    /**
     * @throw Floppy\Common\StorageException
     */
    public function checkRule(Request $request, $object = null);
} 