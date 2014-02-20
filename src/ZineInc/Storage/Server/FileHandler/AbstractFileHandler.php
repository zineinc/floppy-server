<?php

namespace ZineInc\Storage\Server\FileHandler;

use ZineInc\Storage\Common\FileHandler\PathMatcher;
use ZineInc\Storage\Common\FileId;
use ZineInc\Storage\Server\FileSource;
use ZineInc\Storage\Server\FileType;

abstract class AbstractFileHandler implements FileHandler
{
    const TYPE = 'f';

    private $variantMatcher;

    public function __construct(PathMatcher $variantMatcher)
    {
        $this->variantMatcher = $variantMatcher;
    }

    public function match($variantFilepath)
    {
        return $this->variantMatcher->match($variantFilepath);
    }

    public function beforeSendProcess(FileSource $file, FileId $fileId)
    {
        return $file;
    }

    public function beforeStoreProcess(FileSource $file)
    {
        return $file;
    }

    public function getStoreAttributes(FileSource $file)
    {
        $content = $file->content();

        return array(
            'mime-type' => $file->fileType()->mimeType(),
            'extension' => $file->fileType()->prefferedExtension(),
            'size' => strlen($content),
        ) + $this->doGetStoreAttributes($file, $content);
    }

    protected function doGetStoreAttributes(FileSource $file, $content)
    {
        return array();
    }

    public function supports(FileType $fileType)
    {
        return in_array($fileType->mimeType(), $this->supportedMimeTypes());
    }

    protected abstract function supportedMimeTypes();

    public function type()
    {
        return static::TYPE;
    }
}