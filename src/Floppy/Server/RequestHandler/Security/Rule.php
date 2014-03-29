<?php


namespace Floppy\Server\RequestHandler\Security;


use Floppy\Common\FileId;
use Floppy\Common\FileSource;
use Symfony\Component\HttpFoundation\Request;

interface Rule
{
    public function checkFileSource(Request $request, FileSource $fileSource);
    public function checkFileId(Request $request, FileId $fileId);
} 