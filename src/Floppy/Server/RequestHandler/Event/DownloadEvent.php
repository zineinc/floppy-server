<?php

namespace Floppy\Server\RequestHandler\Event;

use Floppy\Common\FileId;
use Floppy\Common\FileSource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DownloadEvent extends \Symfony\Component\EventDispatcher\Event
{
    private $response;
    private $fileId;
    private $fileSource;
    private $fileHandlerName;
    private $request;

    public function __construct(FileId $fileId, Request $request, $fileHandlerName, FileSource $fileSource = null, Response $response = null)
    {
        $this->fileHandlerName = $fileHandlerName;
        $this->fileId = $fileId;
        $this->request = $request;
        $this->fileSource = $fileSource;
        $this->response = $response;
    }

    /**
     * @return string
     */
    public function getFileHandlerName()
    {
        return $this->fileHandlerName;
    }

    /**
     * @return FileId
     */
    public function getFileId()
    {
        return $this->fileId;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return FileSource
     */
    public function getFileSource()
    {
        return $this->fileSource;
    }
}