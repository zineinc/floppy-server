<?php

namespace ZineInc\Storage\Tests\Server\RequestHandler;

use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use ZineInc\Storage\Server\RequestHandler\RequestHandler;

abstract class DownloadRequestHandlerTest extends PHPUnit_Framework_TestCase
{
    const DOWNLOAD_URI = '/some-download-uri';

    /**
     * @var RequestHandler
     */
    private $requestHandler;
    private $fileHandlers;
    private $storage;

    protected function setUp()
    {
        $this->storage = $this->getMock('ZineInc\Storage\Server\Storage\Storage');
        $this->fileHandlers = array(
            $this->getMock('ZineInc\Storage\Server\FileHandler\FileHandler'),
            $this->getMock('ZineInc\Storage\Server\FileHandler\FileHandler'),
        );

        $this->requestHandler = new RequestHandler($this->storage, $this->getMock('ZineInc\Storage\Server\RequestHandler\FileSourceFactory'), $this->fileHandlers);
    }

    private function createDownloadRequest()
    {
        return Request::create(self::DOWNLOAD_URI);
    }
}