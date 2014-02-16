<?php

namespace ZineInc\Storage\Server\FileHandler;

use ZineInc\Storage\Server\FileId;
use ZineInc\Storage\Server\FileSource;
use ZineInc\Storage\Server\FileType;

abstract class AbstractFileHandler implements FileHandler
{
    const TYPE = 'f';

    private $variantMatcher;

    public function __construct(VariantMatcher $variantMatcher)
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
        $file->stream()->resetInput();
        $content = $file->stream()->read();

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