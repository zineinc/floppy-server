<?php


namespace ZineInc\Storage\Server\RequestHandler\Action;


use Symfony\Component\HttpFoundation\Request;
use ZineInc\Storage\Common\ErrorCodes;
use ZineInc\Storage\Server\FileHandler\FileHandler;
use ZineInc\Storage\Server\RequestHandler\Action\Action;
use ZineInc\Storage\Server\RequestHandler\DownloadResponseFactory;
use ZineInc\Storage\Server\RequestHandler\FileHandlerNotFoundException;
use ZineInc\Storage\Server\Storage\Storage;

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