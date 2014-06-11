<?php


namespace Floppy\Server\RequestHandler\Security;

use Floppy\Common\FileId;
use Floppy\Common\FileSource;
use Floppy\Common\HasFileInfo;
use Symfony\Component\HttpFoundation\Request;

class NullRule implements Rule
{
    public function processRule(Request $request, HasFileInfo $object)
    {
        return $object;
    }
}