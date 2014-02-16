<?php

namespace ZineInc\Storage\Tests\Server\RequestHandler;

use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use ZineInc\Storage\Server\FileSource;
use ZineInc\Storage\Server\FileType;
use ZineInc\Storage\Server\RequestHandler\FileSourceNotFoundException;
use ZineInc\Storage\Server\RequestHandler\RequestHandler;
use ZineInc\Storage\Server\Storage\StoreException;
use ZineInc\Storage\Server\Stream\StringInputStream;

class UploadRequestHandlerTest extends PHPUnit_Framework_TestCase
{
    const FILE_MIME_TYPE = 'text/plain';
    const FILE_EXT = 'txt';

    const FILE_ID = 'abc';
    const FILE_HANDLER_TYPE = 'f';

    private static $attrs = array('a' => 'b');

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
        $this->fileSourceFactory = $this->getMock('ZineInc\Storage\Server\RequestHandler\FileSourceFactory');
        $this->fileHandlers = array(
            $this->getMock('ZineInc\Storage\Server\FileHandler\FileHandler'),
            $this->getMock('ZineInc\Storage\Server\FileHandler\FileHandler'),
        );

        $this->requestHandler = new RequestHandler($this->storage, $this->fileSourceFactory, $this->fileHandlers);
    }

    /**
     * @test
     */
    public function fileSourceExists_storeInStorage()
    {
        //given

        $request = $this->createUploadRequest();

        $fileSource = $this->expectsCreateFileSourceAndFindFileHandler($request);
        $this->expectsStore($fileSource, self::FILE_ID);

        //when

        $response = $this->requestHandler->handle($request);

        //then

        $this->verifyMockObjects();
        $this->assertEquals(200, $response->getStatusCode());

        $actualResponseData = json_decode($response->getContent(), true);
        $expectedAttributes = self::$attrs + array('id' => self::FILE_ID);

        $this->assertTrue(isset($actualResponseData['attributes']));
        $this->assertEquals($expectedAttributes, $actualResponseData['attributes']);
    }

    /**
     * @test
     */
    public function fileSourceNotFound_400response()
    {
        //given

        $request = $this->createUploadRequest();

        $this->expectsFileSourceNotFound();

        $this->expectsFileHandlerUnused($this->fileHandlers[0]);
        $this->expectsFileHandlerUnused($this->fileHandlers[1]);

        $this->expectsNoStore();

        //when

        $response = $this->requestHandler->handle($request);

        //then

        $this->verifyMockObjects();
        $this->assertGreaterThanOrEqual(400, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function fileHandlerNotFound_400response()
    {
        //given

        $request = $this->createUploadRequest();
 
        $fileSource = $this->createFileSource();

        $this->expectsCreateFileSource($request, $fileSource);

        $this->expectsFileHandlerUnused($this->fileHandlers[0]);
        $this->expectsFileHandlerUnused($this->fileHandlers[1]);

        $this->expectsNoStore();

        //when

        $response = $this->requestHandler->handle($request);

        //then

        $this->verifyMockObjects();
        $this->assertGreaterThanOrEqual(400, $response->getStatusCode());
    }
    
    private function expectsCreateFileSourceAndFindFileHandler(Request $request)
    {
        $fileSource = $this->createFileSource();

        $this->expectsCreateFileSource($request, $fileSource);

        $this->expectsFileHandlerProcess($this->fileHandlers[0], $fileSource, self::$attrs, self::FILE_HANDLER_TYPE);
        $this->expectsFileHandlerUnused($this->fileHandlers[1]);

        return $fileSource;
    }

    /**
     * @test
     */
    public function storeEx_500response()
    {
        //given

        $request = $this->createUploadRequest();

        $this->expectsCreateFileSourceAndFindFileHandler($request);
        $this->expectsStoreException();

        //when

        $response = $this->requestHandler->handle($request);

        //then

        $this->verifyMockObjects();
        $this->assertGreaterThanOrEqual(500, $response->getStatusCode());
    }

    private function expectsFileSourceNotFound()
    {
        $this->fileSourceFactory->expects($this->once())
                                ->method('createFileSource')
                                ->will($this->throwException(new FileSourceNotFoundException()));
    }

    private function expectsStoreException()
    {
        $this->storage->expects($this->once())
                    ->method('store')
                    ->will($this->throwException(new StoreException()));
    }
    
    private function createUploadRequest()
    {
        return Request::create('/upload');
    }

    private function createFileSource()
    {
        return new FileSource(new StringInputStream('aa'), new FileType(self::FILE_MIME_TYPE, self::FILE_EXT));
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

    private function expectsNoStore()
    {
        $this->storage->expects($this->never())
                    ->method('store');
    }
}