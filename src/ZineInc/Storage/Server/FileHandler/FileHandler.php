<?php

namespace ZineInc\Storage\Server\FileHandler;

use Symfony\Component\HttpFoundation\Response;
use ZineInc\Storage\Common\AttributesBag;
use ZineInc\Storage\Common\FileHandler\PathMatcher;
use ZineInc\Storage\Common\FileId;
use ZineInc\Storage\Common\FileSource;
use ZineInc\Storage\Common\FileType;

interface FileHandler extends PathMatcher, ResponseFilter
{
    /**
     * @param \ZineInc\Storage\Common\FileSource $fileType
     *
     * @return boolean Does given $file is supported by this handler
     */
    public function supports(FileType $fileType);

    /**
     * @return string Unique type code
     */
    public function type();

    /**
     * @return \ZineInc\Storage\Common\AttributesBag Extra attributes for given file. This attributes should be associative array of primitives
     */
    public function getStoreAttributes(FileSource $file);

    /**
     * Additional file processing before persistent store file
     *
     * @return \ZineInc\Storage\Common\FileSource
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