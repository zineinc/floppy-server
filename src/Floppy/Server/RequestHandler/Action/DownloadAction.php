<?php


namespace Floppy\Server\RequestHandler\Action;


use Symfony\Component\HttpFoundation\Request;
use Floppy\Common\ErrorCodes;
use Floppy\Server\FileHandler\FileHandler;
use Floppy\Server\RequestHandler\Action\Action;
use Floppy\Server\RequestHandler\DownloadResponseFactory;
use Floppy\Server\RequestHandler\FileHandlerNotFoundException;
use Floppy\Server\Storage\Storage;

class DownloadAction implements Action
{
    private $fileHandlers;
    private $storage;
    private $downloadResponseFactory;

    function __construct(Storage $storage, DownloadResponseFactory $downloadResponseFactory, array $fileHandlers)
    {
        $this->downloadResponseFactory = $downloadResponseFactory;
        $this->fileHandlers = $fileHandlers;
        $this->storage = $storage;
    }

    public function execute(Request $request)
    {
        $path = rtrim($request->getPathInfo(), '/').($request->getQueryString() ? '?'.$request->getQueryString() : '');
        $handler = $this->findFileHandlerMatches($path);

        $fileId = $handler->match($path);

        if ($this->storage->exists($fileId)) {
            $processedFileSource = $this->storage->getSource($fileId);
        } else {
            $fileSource = $this->storage->getSource($fileId->original());

            $processedFileSource = $handler->beforeSendProcess($fileSource, $fileId);

            if ($processedFileSource !== $fileSource) {
                $this->storage->store($processedFileSource, $fileId);
            }
        }

        $response = $this->downloadResponseFactory->createResponse($processedFileSource);
        $handler->filterResponse($response, $processedFileSource, $fileId);

        return $response;
    }


    /**
     * @return FileHandler
     */
    private function findFileHandlerMatches($path)
    {
        foreach ($this->fileHandlers as $handler) {
            if ($handler->matches($path)) {
                return $handler;
            }
        }

        throw new FileHandlerNotFoundException('file not found', ErrorCodes::FILE_NOT_FOUND);
    }
}