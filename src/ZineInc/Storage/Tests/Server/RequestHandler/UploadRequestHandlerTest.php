<?php

namespace ZineInc\Storage\Tests\Server\RequestHandler;

use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use ZineInc\Storage\Common\FileSource;
use ZineInc\Storage\Common\FileType;
use ZineInc\Storage\Server\RequestHandler\FileSourceNotFoundException;
use ZineInc\Storage\Server\RequestHandler\RequestHandler;
use ZineInc\Storage\Server\Storage\StoreException;
use ZineInc\Storage\Common\Stream\StringInputStream;
use ZineInc\Storage\Tests\Common\Stub\ChecksumChecker;
use ZineInc\Storage\Tests\Server\Stub\FirewallStub;

class UploadRequestHandlerTest extends PHPUnit_Framework_TestCase
{
    const VALID_CHECKSUM = 'valid-checksum';

    const FILE_MIME_TYPE = 'text/plain';
    const FILE_EXT = 'txt';

    const FILE_ID = 'abc';
    const FILE_HANDLER_TYPE1 = 'f';
    const FILE_HANDLER_TYPE2 = 'f2';

    private static $attrs = array('a' => 'b');

    /**
     * @var RequestHandler
     */
    private $requestHandler;
    private $fileHandlers;
    private $fileSourceFactory;
    private $storage;
    private $checksumChecker;

    protected function setUp()
    {
        $this->storage = $this->getMock('ZineInc\Storage\Server\Storage\Storage');
        $this->fileSourceFactory = $this->getMock('ZineInc\Storage\Server\RequestHandler\FileSourceFactory');
        $this->fileHandlers = array(
            self::FILE_HANDLER_TYPE1 => $this->getMock('ZineInc\Storage\Server\FileHandler\FileHandler'),
            self::FILE_HANDLER_TYPE2 => $this->getMock('ZineInc\Storage\Server\FileHandler\FileHandler'),
        );
        $this->checksumChecker = $this->getMock('ZineInc\Storage\Common\ChecksumChecker');

        $this->requestHandler = new RequestHandler(
            $this->storage,
            $this->fileSourceFactory,
            $this->fileHandlers,
            $this->getMock('ZineInc\Storage\Server\RequestHandler\DownloadResponseFactory'),
            new FirewallStub(),
            $this->checksumChecker
        );
    }

    /**
     * @test
     */
    public function fileSourceExists_storeInStorage()
    {
        //given

        $request = $this->createUploadRequest();
        $expectedAttributes = self::$attrs + array('id' => self::FILE_ID, 'type' => self::FILE_HANDLER_TYPE1);

        $fileSource = $this->expectsCreateFileSourceAndFindFileHandler($request);
        $this->expectsStore($fileSource, self::FILE_ID);
        $this->expectsMakeChecksum($expectedAttributes);

        //when

        $response = $this->requestHandler->handle($request);

        //then

        $this->verifyMockObjects();
        $this->assertEquals(200, $response->getStatusCode());

        $actualResponseData = json_decode($response->getContent(), true);

        $this->assertTrue(isset($actualResponseData['attributes']));
        $this->assertEquals($expectedAttributes + array('checksum' => self::VALID_CHECKSUM), $actualResponseData['attributes']);
    }

    private function expectsMakeChecksum($data)
    {
        $this->checksumChecker->expects($this->atLeastOnce())
            ->method('generateChecksum')
            ->with($data)
            ->will($this->returnValue(self::VALID_CHECKSUM));
    }

    /**
     * @test
     */
    public function fileSourceNotFound_400response()
    {
        //given

        $request = $this->createUploadRequest();

        $this->expectsFileSourceNotFound();

        $this->expectsFileHandlerUnused($this->fileHandlers[self::FILE_HANDLER_TYPE1]);
        $this->expectsFileHandlerUnused($this->fileHandlers[self::FILE_HANDLER_TYPE2]);

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

        $this->expectsFileHandlerUnused($this->fileHandlers[self::FILE_HANDLER_TYPE1]);
        $this->expectsFileHandlerUnused($this->fileHandlers[self::FILE_HANDLER_TYPE2]);

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

        $this->expectsFileHandlerProcess($this->fileHandlers[self::FILE_HANDLER_TYPE1], $fileSource, self::$attrs);
        $this->expectsFileHandlerUnused($this->fileHandlers[self::FILE_HANDLER_TYPE2]);

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
        return Request::create('http://localhost/upload');
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

    private function expectsFileHandlerProcess($fileHandler, FileSource $fileSource, array $attrs)
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