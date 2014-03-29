<?php


namespace Floppy\Server\RequestHandler\Security;

use Floppy\Common\FileId;
use Floppy\Common\FileSource;
use Symfony\Component\HttpFoundation\Request;

class NullRule implements Rule
{
    public function checkFileSource(Request $request, FileSource $fileSource)
    {
    }

    public function checkFileId(Request $request, FileId $fileId)
    {
    }
}