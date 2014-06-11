<?php


namespace Floppy\Server\RequestHandler\Action;


use Floppy\Common\FileId;
use Floppy\Common\FileSource;
use Floppy\Server\FileHandler\FileHandlerProvider;
use Floppy\Server\RequestHandler\Event\DownloadEvent;
use Floppy\Server\RequestHandler\Event\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Floppy\Server\FileHandler\FileHandler;
use Floppy\Server\RequestHandler\Action\Action;
use Floppy\Server\RequestHandler\DownloadResponseFactory;
use Floppy\Server\RequestHandler\Exception\FileHandlerNotFoundException;
use Floppy\Server\Storage\Storage;
use Floppy\Server\RequestHandler\Security;
use Symfony\Component\HttpFoundation\Response;

class DownloadAction implements Action
{
    private $fileHandlerProvider;
    private $storage;
    private $downloadResponseFactory;
    private $eventDispatcher;
    private $securityRule;

    public function __construct(Storage $storage, DownloadResponseFactory $downloadResponseFactory, array $fileHandlers, EventDispatcherInterface $eventDispatcher, Security\Rule $securityRule = null)
    {
        $this->downloadResponseFactory = $downloadResponseFactory;
        $this->fileHandlerProvider = new FileHandlerProvider($fileHandlers);
        $this->storage = $storage;
        $this->eventDispatcher = $eventDispatcher;
        $this->securityRule = $securityRule ?: new Security\NullRule();
    }

    public function execute(Request $request)
    {
        $path = rtrim($request->getPathInfo(), '/').($request->getQueryString() ? '?'.$request->getQueryString() : '');

        $handlerName = $this->fileHandlerProvider->findFileHandlerNameMatches($path);
        $handler = $this->fileHandlerProvider->getFileHandler($handlerName);

        $fileId = $handler->match($path);

        $this->securityRule->processRule($request, $fileId);

        $response = $this->dispatchPreProcessingEvent($request, $fileId, $handlerName);
        //skip processing when event delivered response
        if($response !== null) return $response;

        $processedFileSource = $this->getProcessedFileSource($fileId, $handler);

        $response = $this->downloadResponseFactory->createResponse($processedFileSource);
        $handler->filterResponse($response, $processedFileSource, $fileId);

        return $this->dispatchPostProcessingEvent($request, $response, $fileId, $handlerName, $processedFileSource);
    }

    /**
     * @param FileId $fileId
     * @param FileHandler $handler
     * @return \Floppy\Common\FileSource
     */
    private function getProcessedFileSource(FileId $fileId, FileHandler $handler)
    {
        if ($this->storage->exists($fileId)) {
            $processedFileSource = $this->storage->getSource($fileId);
            return $processedFileSource;
        } else {
            $fileSource = $this->storage->getSource($fileId->original());

            $processedFileSource = $handler->beforeSendProcess($fileSource, $fileId);

            if ($processedFileSource !== $fileSource) {
                $this->storage->store($processedFileSource, $fileId);
                return $processedFileSource;
            }
            return $processedFileSource;
        }
    }

    /**
     * @return string
     */
    public static function name()
    {
        return 'download';
    }

    /**
     * @return Response
     */
    private function dispatchPreProcessingEvent(Request $request, $fileId, $handlerName)
    {
        $event = new DownloadEvent($fileId, $request, $handlerName);
        $this->eventDispatcher->dispatch(Events::PRE_DOWNLOAD_FILE_PROCESSING, $event);
        return $event->getResponse();
    }

    /**
     * @return Response
     */
    private function dispatchPostProcessingEvent(Request $request, Response $response, FileId $fileId, $handlerName, FileSource $processedFileSource)
    {
        $event = new DownloadEvent($fileId, $request, $handlerName, $processedFileSource, $response);
        $this->eventDispatcher->dispatch(Events::POST_DOWNLOAD_FILE_PROCESSING, $event);
        return $event->getResponse();
    }
}