<?php

namespace ZineInc\Storage\Tests\Server\RequestHandler;

use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ZineInc\Storage\Common\FileHandler\PathMatchingException;
use ZineInc\Storage\Common\FileId;
use ZineInc\Storage\Server\FileHandler\FileProcessException;
use ZineInc\Storage\Server\FileSource;
use ZineInc\Storage\Server\FileType;
use ZineInc\Storage\Server\RequestHandler\DownloadResponseFactory;
use ZineInc\Storage\Server\RequestHandler\RequestHandler;
use ZineInc\Storage\Server\Storage\FileSourceNotFoundException;
use ZineInc\Storage\Server\Stream\StringInputStream;

class DownloadRequestHandlerTest extends PHPUnit_Framework_TestCase
{
    const DOWNLOAD_URI = '/some-download-uri/some-file.jpg?a=1';
    const SOME_ID = 'some-id';

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


        $this->requestHandler = new RequestHandler(
            $this->storage,
            $this->getMock('ZineInc\Storage\Server\RequestHandler\FileSourceFactory'),
            $this->fileHandlers,
            new DownloadRequestHandlerTest_DownloadResponseFactory($this->createResponse())
        );
    }

    /**
     * @test
     */
    public function fileHandlerFound_fileExists_processSuccess_returnOkResponse()
    {
        //given

        $fileSource = $this->createFileSource();
        $fileId = $this->createProcessedFileId();

        $this->expectsSuccessFileHandler($this->fileHandlers[0], $fileId, $fileSource);

        $this->expectsProcessedFileDoesNotExistsInStorage($fileId);
        $this->expectsGetFileSourceFromStorage($fileId->original(), $fileSource);
        $this->expectsDontStoreFileVariant();

        //when

        $actualResponse = $this->requestHandler->handle($this->createDownloadRequest());

        //then

        $this->verifyMockObjects();
        $this->assertEquals($this->createResponse(), $actualResponse);
    }

    /**
     * @test
     */
    public function fileHandlerFound_fileExists_processSuccesAndFileSourceChanges_storeFileVariant_returnOkResponse()
    {
        //given

        $fileSource = $this->createFileSource();
        $processedFileSource = $this->createFileSource('processed-content');
        $fileId = $this->createProcessedFileId();

        $this->expectsFileHandlerMatchesAndMatch($this->fileHandlers[0], $fileId);
        $this->expectsProcessedFileDoesNotExistsInStorage($fileId);
        $this->expectsGetFileSourceFromStorage($fileId->original(), $fileSource);
        $this->expectsFileHandlerSuccessProcess($this->fileHandlers[0], $fileId, $fileSource, $processedFileSource);
        $this->expectsFileHandlerFilterResponse($this->fileHandlers[0], $fileId, $processedFileSource);

        $this->storage->expects($this->once())
            ->method('store')
            ->with($processedFileSource, $fileId);


        //when

        $actualResponse = $this->requestHandler->handle($this->createDownloadRequest());

        //then

        $this->verifyMockObjects();
        $this->assertEquals($this->createResponse(), $actualResponse);
    }

    /**
     * @test
     */
    public function fileHandlerFound_fileExists_processFailed_returnBadResponse()
    {
        //given

        $fileSource = $this->createFileSource();
        $fileId = $this->createProcessedFileId();

        $this->expectsFileHandlerMatchesAndMatch($this->fileHandlers[0], $fileId);
        $this->expectsProcessedFileDoesNotExistsInStorage($fileId);
        $this->expectsGetFileSourceFromStorage($fileId->original(), $fileSource);
        $this->expectsFileHandlerErrorProcess($this->fileHandlers[0]);

        $this->expectsDontStoreFileVariant();

        //when

        $actualResponse = $this->requestHandler->handle($this->createDownloadRequest());

        //then

        $this->verifyMockObjects();
        $this->assertNotNull($actualResponse);
        $this->assertGreaterThanOrEqual(500, $actualResponse->getStatusCode());
    }

    /**
     * @test
     */
    public function fileHandlerFound_fileNotExist_returnBadResponse()
    {
        //given

        $fileId = $this->createProcessedFileId();
        $this->expectsFileHandlerMatchesAndMatch($this->fileHandlers[0], $fileId);
        $this->expectsFileSourceNotFound();

        //when

        $actualResponse = $this->requestHandler->handle($this->createDownloadRequest());

        //then

        $this->verifyMockObjects();
        $this->assertNotNull($actualResponse);
        $this->assertGreaterThanOrEqual(400, $actualResponse->getStatusCode());
    }

    /**
     * @test
     */
    public function fileHandlerNotFound_returnBadResponse()
    {
        //given

        $this->expectsFileHandlerDoesntMatch($this->fileHandlers[0]);
        $this->expectsFileHandlerDoesntMatch($this->fileHandlers[1]);

        //when

        $actualResponse = $this->requestHandler->handle($this->createDownloadRequest());

        //then

        $this->verifyMockObjects();
        $this->assertNotNull($actualResponse);
        $this->assertGreaterThanOrEqual(400, $actualResponse->getStatusCode());
    }

    /**
     * @test
     */
    public function fileHandlerFound_fileHandlerMatchError_returnBadResponse()
    {
        //given

        $this->expectsFileHandlerMatches($this->fileHandlers[0]);
        $this->fileHandlers[0]->expects($this->atLeastOnce())
            ->method('match')
            ->will($this->throwException(new PathMatchingException()));

        //when

        $actualResponse = $this->requestHandler->handle($this->createDownloadRequest());

        //then

        $this->verifyMockObjects();
        $this->assertNotNull($actualResponse);
        $this->assertGreaterThanOrEqual(400, $actualResponse->getStatusCode());
    }

    /**
     * @test
     */
    public function fileHandlerFound_processedFileAlreadyExist_skipProcessing_returnOkResponse()
    {
        //given

        $fileId = $this->createProcessedFileId();
        $fileSource = $this->createFileSource();

        $this->expectsFileHandlerMatchesAndMatch($this->fileHandlers[0], $fileId);
        $this->expectsProcessedFileExistsInStorage($fileId);
        $this->expectsSkipFileHandlerProcess($this->fileHandlers[0]);
        $this->expectsDontStoreFileVariant();
        $this->expectsGetFileSourceFromStorage($fileId, $fileSource);
        $this->expectsFileHandlerFilterResponse($this->fileHandlers[0], $fileId, $fileSource);

        //when

        $actualResponse = $this->requestHandler->handle($this->createDownloadRequest());

        //then

        $this->verifyMockObjects();
        $this->assertEquals($this->createResponse(), $actualResponse);
    }

    private function createResponse()
    {
        return new Response('some-content');
    }

    private function createProcessedFileId()
    {
        return new FileId(self::SOME_ID, array('some-val' => 'val'));
    }

    private function createFileSource($content = 'some')
    {
        return new FileSource(new StringInputStream($content), new FileType('text/plain', 'text'));
    }

    private function createDownloadRequest()
    {
        return Request::create(self::DOWNLOAD_URI);
    }

    private function expectsSuccessFileHandler($handler, $fileId, $fileSource)
    {
        $this->expectsFileHandlerMatchesAndMatch($handler, $fileId);
        $this->expectsFileHandlerSuccessProcess($handler, $fileId, $fileSource);
        $this->expectsFileHandlerFilterResponse($handler, $fileId, $fileSource);
    }

    /**
     * @param $fileId
     * @param $fileSource
     */
    private function expectsGetFileSourceFromStorage($fileId, $fileSource)
    {
        $this->storage->expects($this->atLeastOnce())
            ->method('getSource')
            ->with($fileId)
            ->will($this->returnValue($fileSource));
    }

    private function expectsProcessedFileExistsInStorage($fileId)
    {
        $this->storage->expects($this->atLeastOnce())
            ->method('exists')
            ->with($fileId)
            ->will($this->returnValue(true));
    }

    private function expectsDontStoreFileVariant()
    {
        $this->storage->expects($this->never())
            ->method('store');
    }

    /**
     * @param $handler
     * @param $fileId
     */
    private function expectsFileHandlerMatchesAndMatch($handler, $fileId)
    {
        $this->expectsFileHandlerMatches($handler);
        $this->expectsFileHandlerMatch($handler, $fileId);
    }


    /**
     * @param $handler
     * @param $fileId
     * @param $fileSource
     */
    private function expectsFileHandlerSuccessProcess($handler, $fileId, $fileSource, $processedFileSource = null)
    {
        $handler->expects($this->once())
            ->method('beforeSendProcess')
            ->with($fileSource, $fileId)
            ->will($this->returnValue($processedFileSource ? : $fileSource));
    }

    /**
     * @param $handler
     * @param $fileId
     * @param $fileSource
     * @param $response
     */
    private function expectsFileHandlerFilterResponse($handler, $fileId, $fileSource)
    {
        $handler->expects($this->any())
            ->method('filterResponse')
            ->with($this->createResponse(), $fileSource, $fileId);
    }

    private function expectsFileHandlerErrorProcess($handler)
    {
        $handler->expects($this->once())
            ->method('beforeSendProcess')
            ->will($this->throwException(new FileProcessException()));
    }

    private function expectsSkipFileHandlerProcess($handler)
    {
        $handler->expects($this->never())
            ->method('beforeSendProcess');
    }

    private function expectsFileSourceNotFound()
    {
        $this->storage->expects($this->once())
            ->method('getSource')
            ->will($this->throwException(new FileSourceNotFoundException()));
    }

    private function expectsFileHandlerDoesntMatch($handler)
    {
        $handler->expects($this->any())
            ->method('matches')
            ->will($this->returnValue(false));
        $handler->expects($this->never())
            ->method('match');
    }

    /**
     * @param $handler
     */
    private function expectsFileHandlerMatches($handler)
    {
        $handler->expects($this->any())
            ->method('matches')
            ->with(self::DOWNLOAD_URI)
            ->will($this->returnValue(true));
    }

    /**
     * @param $handler
     * @param $fileId
     */
    private function expectsFileHandlerMatch($handler, $fileId)
    {
        $handler->expects($this->atLeastOnce())
            ->method('match')
            ->with(self::DOWNLOAD_URI)
            ->will($this->returnValue($fileId));
    }

    /**
     * @param $fileId
     */
    private function expectsProcessedFileDoesNotExistsInStorage($fileId)
    {
        $this->storage->expects($this->atLeastOnce())
            ->method('exists')
            ->with($fileId)
            ->will($this->returnValue(false));
    }
}

class DownloadRequestHandlerTest_DownloadResponseFactory implements DownloadResponseFactory
{
    private $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function createResponse(FileSource $fileSource)
    {
        return $this->response;
    }
}