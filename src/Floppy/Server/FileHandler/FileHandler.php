<?php

namespace Floppy\Server\FileHandler;

use Symfony\Component\HttpFoundation\Response;
use Floppy\Common\AttributesBag;
use Floppy\Common\FileHandler\PathMatcher;
use Floppy\Common\FileId;
use Floppy\Common\FileSource;
use Floppy\Common\FileType;

interface FileHandler extends PathMatcher, ResponseFilter
{
    /**
     * @param \Floppy\Common\FileSource $fileType
     *
     * @return boolean Does given $file is supported by this handler
     */
    public function supports(FileType $fileType);

    /**
     * @return \Floppy\Common\AttributesBag Extra attributes for given file. This attributes should be associative array of primitives
     */
    public function getStoreAttributes(FileSource $file);

    /**
     * Additional file processing before persistent store file
     *
     * @return \Floppy\Common\FileSource
     */
    public function beforeStoreProcess(FileSource $file);

    /**
     * Additional file processing before file download
     *
     * @param FileSource $file
     * @param AttributesBag $attributes Supported attributes depends on handler type
     *
     * @return FileSource
     */
    public function beforeSendProcess(FileSource $file, FileId $fileId);
}