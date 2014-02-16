<?php

namespace ZineInc\Storage\Tests\Server;

use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use ZineInc\Storage\Server\FileSource;
use ZineInc\Storage\Server\FileType;
use ZineInc\Storage\Server\RequestHandler;
use ZineInc\Storage\Server\Stream\StringStream;

class RequestHandlerTest extends PHPUnit_Framework_TestCase
{
    const FILE_MIME_TYPE = 'text/plain';
    const FILE_EXT = 'txt';

    const FILE_ID = 'abc';
    const FILE_HANDLER_TYPE = 'f';

    const UNSUPPORTED_MIME_TYPE = 'text/plain3';

    /**
     * @var RequestHandler
     */
    private $requestHandler;
    private $fileHandlers;
    private $fileSourceFactory;
    private $storage;

    protected function setUp()
    {
        $this->storage = $this->getMock('ZineInc\Storage\Server\Storage\Storage');
        $this->fileSourceFactory = $this->getMock('ZineInc\Storage\Server\FileSourceFactory');
        $this->fileHandlers = array(
            $this->getMock('ZineInc\Storage\Server\FileHandler\FileHandler'),
            $this->getMock('ZineInc\Storage\Server\FileHandler\FileHandler'),
        );

        $this->requestHandler = new RequestHandler($this->storage, $this->fileSourceFactory, $this->fileHandlers);
    }

    /**
     * @test
     */
    public function uploadRequest_fileSourceExists_storeInStorage()
    {
        //given

        $request = $this->createUploadRequest();
        $fileSource = $this->createFileSource();
        $attrs = array('a' => 'b');

        $this->expectsCreateFileSource($request, $fileSource);

        $this->expectsFileHandlerProcess($this->fileHandlers[0], $fileSource, $attrs, self::FILE_HANDLER_TYPE);
        $this->expectsFileHandlerUnused($this->fileHandlers[1]);

        $this->expectsStore($fileSource, self::FILE_ID);

        //when

        $response = $this->requestHandler->handle($request);

        //then

        $this->verifyMockObjects();
        $this->assertEquals(200, $response->getStatusCode());

        $actualResponseData = json_decode($response->getContent(), true);
        $expectedResponseData = $attrs + array('id' => self::FILE_ID);

        $this->assertEquals($expectedResponseData, $actualResponseData);
    }
    
    private function createUploadRequest()
    {
        return Request::create('/upload');
    }

    private function createFileSource()
    {
        return new FileSource(new StringStream('aa'), new FileType(self::FILE_MIME_TYPE, self::FILE_EXT));
    }

    private function expectsCreateFileSource(Request $request, FileSource $fileSource)
    {
        $this->fileSourceFactory->expects($this->once())
                                ->method('createFileSource')
                                ->with($request)
                                ->will($this->returnValue($fileSource));
    }

    private function expectsFileHandlerProcess($fileHandler, FileSource $fileSource, array $attrs, $type)
    {
        $fileHandler->expects($this->once())
                    ->method('beforeStoreProcess')
                    ->with($fileSource)
                    ->will($this->returnValue($fileSource));
        $fileHandler->expects($this->any())
                    ->method('supports')
                    ->with($fileSource->fileType())
                    ->will($this->returnValue(true));
        $fileHandler->expects($this->atLeastOnce())
                    ->method('getStoreAttributes')
                    ->with($fileSource)
                    ->will($this->returnValue($attrs));
        $fileHandler->expects($this->any())
                    ->method('type')
                    ->will($this->returnValue($type));
    }

    private function expectsFileHandlerUnused($fileHandler)
    {
        $fileHandler->expects($this->never())
                    ->method('beforeStoreProcess');
        $fileHandler->expects($this->never())
                    ->method('getStoreAttributes');
        $fileHandler->expects($this->never())
                    ->method('type');
        $fileHandler->expects($this->any())
                    ->method('supports')
                    ->will($this->returnValue(false));
    }

    private function expectsStore(FileSource $fileSource, $id)
    {
        $this->storage->expects($this->once())
                    ->method('store')
                    ->with($fileSource)
                    ->will($this->returnValue($id));
    }
}