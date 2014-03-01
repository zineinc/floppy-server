<?php

namespace ZineInc\Storage\Server\FileHandler;

use Symfony\Component\HttpFoundation\Response;
use ZineInc\Storage\Common\FileHandler\PathMatcher;
use ZineInc\Storage\Common\FileId;
use ZineInc\Storage\Common\FileSource;
use ZineInc\Storage\Common\FileType;

abstract class AbstractFileHandler implements FileHandler
{
    private $pathMatcher;
    private $responseFilters;

    public function __construct(PathMatcher $pathMatcher, array $responseFilters)
    {
        $this->pathMatcher = $pathMatcher;
        $this->responseFilters = $responseFilters;
    }

    public function match($variantFilepath)
    {
        return $this->pathMatcher->match($variantFilepath);
    }

    public function matches($variantFilepath)
    {
        return $this->pathMatcher->matches($variantFilepath);
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
        return in_array($fileType->mimeType(), $this->supportedMimeTypes()) && in_array($fileType->prefferedExtension(), $this->supportedExtensions());
    }

    protected abstract function supportedMimeTypes();

    protected abstract function supportedExtensions();

    public function filterResponse(Response $response, FileSource $fileSource, FileId $fileId)
    {
        foreach($this->responseFilters as $filter) {
            $filter->filterResponse($response, $fileSource, $fileId);
        }
    }
}