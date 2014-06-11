<?php


namespace Floppy\Tests\Server\RequestHandler\Action;


use Floppy\Common\FileSource;
use Floppy\Common\FileType;
use Floppy\Common\Stream\StringInputStream;
use Floppy\Server\RequestHandler\Action\UploadAction;
use Floppy\Server\RequestHandler\Exception\FileSourceNotFoundException;
use Floppy\Server\RequestHandler\Security\Rule;
use Floppy\Server\Storage\Exception\StoreException;
use Floppy\Tests\Server\Stub\SecurityRuleStub;
use Symfony\Component\HttpFoundation\Request;

class UploadActionTest extends \PHPUnit_Framework_TestCase
{
    const VALID_CHECKSUM = 'valid-checksum';

    const FILE_MIME_TYPE = 'text/plain';
    const FILE_EXT = 'txt';

    const FILE_ID = 'abc';
    const FILE_HANDLER_TYPE1 = 'f';
    const FILE_HANDLER_TYPE2 = 'f2';

    private static $attrs = array('a' => 'b');

    private $fileHandlers;
    private $fileSourceFactory;
    private $storage;
    private $checksumChecker;

    protected function setUp()
    {
        $this->storage = $this->getMock('Floppy\Server\Storage\Storage');
        $this->fileSourceFactory = $this->getMock('Floppy\Server\RequestHandler\FileSourceFactory');
        $this->fileHandlers = array(
            self::FILE_HANDLER_TYPE1 => $this->getMock('Floppy\Server\FileHandler\FileHandler'),
            self::FILE_HANDLER_TYPE2 => $this->getMock('Floppy\Server\FileHandler\FileHandler'),
        );
        $this->checksumChecker = $this->getMock('Floppy\Common\ChecksumChecker');
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

        $response = $this->createAction()->execute($request);

        //then

        $this->verifyMockObjects();
        $this->assertEquals(200, $response->getStatusCode());

        $actualResponseData = json_decode($response->getContent(), true);

        $this->assertTrue(isset($actualResponseData['attributes']));
        $this->assertEquals($expectedAttributes + array('checksum' => self::VALID_CHECKSUM), $actualResponseData['attributes']);
    }

    /**
     * @test
     */
    public function fileSourceExists_shouldBeUsedFileSourceProcessedBySecurityRule()
    {
        //given

        $request = $this->createUploadRequest();

        $fileSource = $this->createFileSource();
        $info = array('name' => 'value');

        $this->expectsCreateFileSource($request, $fileSource);
        $this->expectsFileHandlerProcess($this->fileHandlers[self::FILE_HANDLER_TYPE1], $fileSource->withInfo($info), self::$attrs);
        $this->expectsStore($fileSource->withInfo($info), self::FILE_ID);

        //when

        $this->createAction(new SecurityRuleStub($info))->execute($request);
    }

    /**
     * @test
     * @expectedException Floppy\Server\RequestHandler\Exception\FileSourceNotFoundException
     */
    public function fileSourceNotFound_throwEx()
    {
        //given

        $request = $this->createUploadRequest();

        $this->expectsFileSourceNotFound();

        $this->expectsFileHandlerUnused($this->fileHandlers[self::FILE_HANDLER_TYPE1]);
        $this->expectsFileHandlerUnused($this->fileHandlers[self::FILE_HANDLER_TYPE2]);

        $this->expectsNoStore();

        //when

        $this->createAction()->execute($request);
    }

    /**
     * @test
     * @expectedException Floppy\Server\FileHandler\Exception\FileHandlerNotFoundException
     */
    public function fileHandlerNotFound_throwEx()
    {
        //given

        $request = $this->createUploadRequest();

        $fileSource = $this->createFileSource();

        $this->expectsCreateFileSource($request, $fileSource);

        $this->expectsFileHandlerUnused($this->fileHandlers[self::FILE_HANDLER_TYPE1]);
        $this->expectsFileHandlerUnused($this->fileHandlers[self::FILE_HANDLER_TYPE2]);

        $this->expectsNoStore();

        //when

        $this->createAction()->execute($request);
    }

    /**
     * @test
     * @expectedException Floppy\Server\Storage\Exception\StoreException
     */
    public function storeEx_throwEx()
    {
        //given

        $request = $this->createUploadRequest();

        $this->expectsCreateFileSourceAndFindFileHandler($request);
        $this->expectsStoreException();

        //when

        $this->createAction()->execute($request);
    }

    private function expectsMakeChecksum($data)
    {
        $this->checksumChecker->expects($this->atLeastOnce())
            ->method('generateChecksum')
            ->with($data)
            ->will($this->returnValue(self::VALID_CHECKSUM));
    }

    private function expectsCreateFileSourceAndFindFileHandler(Request $request)
    {
        $fileSource = $this->createFileSource();

        $this->expectsCreateFileSource($request, $fileSource);

        $this->expectsFileHandlerProcess($this->fileHandlers[self::FILE_HANDLER_TYPE1], $fileSource, self::$attrs);
        $this->expectsFileHandlerUnused($this->fileHandlers[self::FILE_HANDLER_TYPE2]);

        return $fileSource;
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

    private function createAction(Rule $securityRule = null)
    {
        return new UploadAction($this->storage, $this->fileSourceFactory, $this->fileHandlers, $this->checksumChecker, $securityRule);
    }
}
 