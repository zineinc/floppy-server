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
use ZineInc\Storage\Common\FileSource;
use ZineInc\Storage\Server\RequestHandler\Security\Firewall;
use ZineInc\Storage\Server\Storage\Storage;
use ZineInc\Storage\Server\Storage\StoreException;
use ZineInc\Storage\Common\StorageError;
use ZineInc\Storage\Common\StorageException;
use ZineInc\Storage\Server\RequestHandler\Action\Action;
use ZineInc\Storage\Server\RequestHandler\Action\DownloadAction;
use ZineInc\Storage\Server\RequestHandler\Action\UploadAction;

class RequestHandler implements LoggerAwareInterface
{
    const UPLOAD_ACTION = 'upload';
    const DOWNLOAD_ACTION = 'download';
    const DELETE_ACTION = 'delete';

    /**
     * @var LoggerInterface
     */
    private $logger;
    private $storage;
    private $fileSourceFactory;
    private $fileHandlers;
    private $downloadResponseFactory;
    private $firewall;

    private $actions;

    public function __construct(Storage $storage, FileSourceFactory $fileSourceFactory, array $handlers, DownloadResponseFactory $downloadResponseFactory, Firewall $firewall)
    {
        $this->storage = $storage;
        $this->fileSourceFactory = $fileSourceFactory;
        $this->fileHandlers = $handlers;
        $this->logger = new NullLogger();
        $this->downloadResponseFactory = $downloadResponseFactory;
        $this->firewall = $firewall;

        $this->actions = array(
            self::UPLOAD_ACTION => new UploadAction($storage, $fileSourceFactory, $handlers),
            self::DOWNLOAD_ACTION => new DownloadAction($storage, $downloadResponseFactory, $handlers),
        );
    }

    /**
     * @return Response
     */
    public function handle(Request $request)
    {
        $actionName = $this->resolveActionName($request);

        if(!isset($this->actions[$actionName])) {
            return new Response(404);
        } else {
            return $this->executeAction($this->actions[$actionName], $request, $actionName);
        }
    }

    private function resolveActionName(Request $request)
    {
        if (rtrim($request->getPathInfo(), '/') === '/upload') {
            return self::UPLOAD_ACTION;
        } else {
            return self::DOWNLOAD_ACTION;
        }
    }

    private function executeAction(Action $action, Request $request, $actionName)
    {
        try {
            $this->firewall->guard($request, $actionName);

            return $action->execute($request);
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
        $codesMap = array(
            ErrorCodes::FILE_NOT_FOUND => 404,
            ErrorCodes::ACCESS_DENIED => 401,
        );

        return isset($codesMap[$errorCode]) ? $codesMap[$errorCode] : 400;
    }

    private function createErrorResponse(StorageException $e, $httpStatusCode)
    {
        return new JsonResponse(array(
            'code' => $e->getCode(),
            'attributes' => null,
        ), $httpStatusCode);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}