<?php


namespace Floppy\Tests\Server\RequestHandler\Action;


use Floppy\Common\FileHandler\PathMatchingException;
use Floppy\Common\FileId;
use Floppy\Common\FileSource;
use Floppy\Common\FileType;
use Floppy\Common\Stream\StringInputStream;
use Floppy\Server\FileHandler\FileProcessException;
use Floppy\Server\RequestHandler\Action\DownloadAction;
use Floppy\Server\RequestHandler\DownloadResponseFactory;
use Floppy\Server\RequestHandler\FileSourceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DownloadActionTest extends \PHPUnit_Framework_TestCase
{
    const DOWNLOAD_URI = '/some-download-uri/some-file.jpg?a=1';
    const SOME_ID = 'some-id';

    private $sampleResponse;
    private $fileHandlers;
    private $storage;

    private $action;

    protected function setUp()
    {
        $this->sampleResponse = new Response('some-content');

        $this->storage = $this->getMock('Floppy\Server\Storage\Storage');
        $this->fileHandlers = array(
            $this->getMock('Floppy\Server\FileHandler\FileHandler'),
            $this->getMock('Floppy\Server\FileHandler\FileHandler'),
        );

        $this->action = new DownloadAction($this->storage, new DownloadActionTest_DownloadResponseFactory($this->getSampleResponse()), $this->fileHandlers);
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

        $actualResponse = $this->action->execute($this->createDownloadRequest());

        //then

        $this->verifyMockObjects();
        $this->assertResponseOk($actualResponse);
    }


    /**
     * @test
     */
    public function fileHandlerFound_fileExists_processSuccessAndFileSourceChanges_storeFileVariant_returnOkResponse()
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

        $actualResponse = $this->action->execute($this->createDownloadRequest());

        //then

        $this->verifyMockObjects();
        $this->assertResponseOk($actualResponse);
    }


    /**
     * @test
     * @expectedException Floppy\Server\FileHandler\FileProcessException
     */
    public function fileHandlerFound_fileExists_processFailed_throwEx()
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

        $this->action->execute($this->createDownloadRequest());
    }

    /**
     * @test
     * @expectedException Floppy\Server\RequestHandler\FileSourceNotFoundException
     */
    public function fileHandlerFound_fileNotExist_throwEx()
    {
        //given

        $fileId = $this->createProcessedFileId();
        $this->expectsFileHandlerMatchesAndMatch($this->fileHandlers[0], $fileId);
        $this->expectsFileSourceNotFound();

        //when

        $this->action->execute($this->createDownloadRequest());
    }

    /**
     * @test
     * @expectedException Floppy\Server\FileHandler\FileHandlerNotFoundException
     */
    public function fileHandlerNotFound_throwEx()
    {
        //given

        $this->expectsFileHandlerDoesntMatch($this->fileHandlers[0]);
        $this->expectsFileHandlerDoesntMatch($this->fileHandlers[1]);

        //when

        $this->action->execute($this->createDownloadRequest());
    }

    /**
     * @test
     * @expectedException Floppy\Common\FileHandler\PathMatchingException
     */
    public function fileHandlerFound_fileHandlerMatchError_throwEx()
    {
        //given

        $this->expectsFileHandlerMatches($this->fileHandlers[0]);
        $this->fileHandlers[0]->expects($this->atLeastOnce())
            ->method('match')
            ->will($this->throwException(new PathMatchingException()));

        //when

        $this->action->execute($this->createDownloadRequest());
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

        $actualResponse = $this->action->execute($this->createDownloadRequest());

        //then

        $this->verifyMockObjects();
        $this->assertResponseOk($actualResponse);
    }

    private function getSampleResponse()
    {
        return $this->sampleResponse;
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
            ->with($this->getSampleResponse(), $fileSource, $fileId);
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

    /**
     * @param $actualResponse
     */
    private function assertResponseOk($actualResponse)
    {
        $this->assertEquals($this->getSampleResponse()->getContent(), $actualResponse->getContent());
    }
}

class DownloadActionTest_DownloadResponseFactory implements DownloadResponseFactory
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