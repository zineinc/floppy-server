<?php

namespace ZineInc\Storage\Server\RequestHandler;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ZineInc\Storage\Server\FileHandler\FileHandler;
use ZineInc\Storage\Server\FileSource;
use ZineInc\Storage\Server\Storage\Storage;
use ZineInc\Storage\Server\StorageError;
use ZineInc\Storage\Server\StorageException;

class RequestHandler implements LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    private $storage;
    private $fileSourceFactory;
    private $fileHandlers;

    public function __construct(Storage $storage, FileSourceFactory $fileSourceFactory, array $handlers)
    {
        $this->storage = $storage;
        $this->fileSourceFactory = $fileSourceFactory;
        $this->fileHandlers = $handlers;
        $this->logger = new NullLogger();
    }

    /**
     * @return Response
     */
    public function handle(Request $request)
    {
        //TODO: check uri
        return $this->handleUploadRequest($request);
    }

    private function handleUploadRequest(Request $request)
    {
        try
        {
            $fileSource = $this->fileSourceFactory->createFileSource($request);

            $fileHandler = $this->findFileHandler($fileSource);

            $fileSource = $fileHandler->beforeStoreProcess($fileSource);
            $attrs = $fileHandler->getStoreAttributes($fileSource);

            $id = $this->storage->store($fileSource);

            $attrs['id'] = $id;

            return new JsonResponse(array(
                'message' => null,
                'attributes' => $attrs,
            ));
        }
        catch(StorageError $e)
        {
            $this->logger->error($e);
            return $this->createErrorResponse($e, 500);
        }
        catch(StorageException $e)
        {
            $this->logger->warning($e);
            return $this->createErrorResponse($e, 400);
        }
    }

    private function createErrorResponse(StorageException $e, $httpStatusCode)
    {
        return new JsonResponse(array(
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'attributes' => null,
        ), $httpStatusCode);
    }

    /**
     * @return FileHandler
     */
    private function findFileHandler(FileSource $fileSource)
    {
        foreach($this->fileHandlers as $fileHandler) {
            if($fileHandler->supports($fileSource->fileType())) {
                return $fileHandler;
            }
        }

        throw new FileHandlerNotFoundException(sprintf('File type "%s" is unsupported', $fileSource->fileType()->mimeType()));
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}