<?php

namespace ZineInc\Storage\Server\RequestHandler;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ZineInc\Storage\Common\FileHandler\PathMatchingException;
use ZineInc\Storage\Common\ErrorCodes;
use ZineInc\Storage\Server\FileHandler\FileHandler;
use ZineInc\Storage\Server\FileSource;
use ZineInc\Storage\Server\Storage\Storage;
use ZineInc\Storage\Server\Storage\StoreException;
use ZineInc\Storage\Common\StorageError;
use ZineInc\Storage\Common\StorageException;

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
        if (rtrim($request->getPathInfo(), '/') === '/upload') {
            return $this->handleUploadRequest($request);
        } else {
            return $this->handleDownloadRequest($request);
        }
    }

    private function handleUploadRequest(Request $request)
    {
        try {
            $fileSource = $this->fileSourceFactory->createFileSource($request);

            $fileHandler = $this->findFileHandler($fileSource);

            $fileSource = $fileHandler->beforeStoreProcess($fileSource);
            $attrs = $fileHandler->getStoreAttributes($fileSource);

            $id = $this->storage->store($fileSource);

            $attrs['id'] = $id;

            return new JsonResponse(array(
                'code' => 0,
                'attributes' => $attrs,
            ));
        } catch (StorageError $e) {
            $this->logger->error($e);
            return $this->createErrorResponse($e, 500);
        } catch (StorageException $e) {
            $this->logger->warning($e);
            return $this->createErrorResponse($e, $this->convertErrorCodeToHttpStatusCode($e->getCode()));
        }
    }

    private function convertErrorCodeToHttpStatusCode($errorCode)
    {
        return $errorCode === ErrorCodes::FILE_NOT_FOUND ? 404 : 400;
    }

    private function createErrorResponse(StorageException $e, $httpStatusCode)
    {
        return new JsonResponse(array(
            'code' => $e->getCode(),
            'attributes' => null,
        ), $httpStatusCode);
    }

    /**
     * @return FileHandler
     */
    private function findFileHandler(FileSource $fileSource)
    {
        foreach ($this->fileHandlers as $fileHandler) {
            if ($fileHandler->supports($fileSource->fileType())) {
                return $fileHandler;
            }
        }

        throw new FileHandlerNotFoundException(sprintf('File type "%s" is unsupported', $fileSource->fileType()->mimeType()));
    }

    private function handleDownloadRequest(Request $request)
    {
        try {
            $path = rtrim($request->getPathInfo(), '/');
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

            return $handler->createResponse($processedFileSource, $fileId);
        } catch (StorageError $e) {
            $this->logger->error($e);
            return $this->createErrorResponse($e, 500);
        } catch (StorageException $e) {
            $this->logger->warning($e);
            return $this->createErrorResponse($e, $this->convertErrorCodeToHttpStatusCode($e->getCode()));
        }
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

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}