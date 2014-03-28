<?php

namespace Floppy\Server\RequestHandler;

use Floppy\Server\RequestHandler\Action\CorsEtcAction;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Floppy\Common\ChecksumChecker;
use Floppy\Common\FileHandler\PathMatchingException;
use Floppy\Common\ErrorCodes;
use Floppy\Server\FileHandler\FileHandler;
use Floppy\Common\FileSource;
use Floppy\Server\RequestHandler\Security\Firewall;
use Floppy\Server\Storage\Storage;
use Floppy\Server\Storage\StoreException;
use Floppy\Common\StorageError;
use Floppy\Common\StorageException;
use Floppy\Server\RequestHandler\Action\Action;
use Floppy\Server\RequestHandler\Action\DownloadAction;
use Floppy\Server\RequestHandler\Action\UploadAction;

class RequestHandler implements LoggerAwareInterface
{
    const UPLOAD_ACTION = 'upload';
    const DOWNLOAD_ACTION = 'download';
    const DELETE_ACTION = 'delete';
    const CORS_ETC_ACTION = 'cors_etc';

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

    public function __construct(Storage $storage, FileSourceFactory $fileSourceFactory, array $handlers, DownloadResponseFactory $downloadResponseFactory, Firewall $firewall, ChecksumChecker $checksumChecker, array $allowedOriginHosts = array())
    {
        $this->storage = $storage;
        $this->fileSourceFactory = $fileSourceFactory;
        $this->fileHandlers = $handlers;
        $this->logger = new NullLogger();
        $this->downloadResponseFactory = $downloadResponseFactory;
        $this->firewall = $firewall;

        $this->actions = array(
            self::UPLOAD_ACTION => new UploadAction($storage, $fileSourceFactory, $handlers, $checksumChecker),
            self::DOWNLOAD_ACTION => new DownloadAction($storage, $downloadResponseFactory, $handlers),
            self::CORS_ETC_ACTION => new CorsEtcAction($allowedOriginHosts),
        );
    }

    /**
     * @return Response
     */
    public function handle(Request $request)
    {
        if($request->isMethod('options')) {
            return new Response();
        }

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
        } elseif($request->isMethod('options') || in_array($request->getPathInfo(), array('/crossdomain.xml', '/clientaccesspolicy.xml'))) {
            return self::CORS_ETC_ACTION;
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
            'message' => ErrorCodes::convertCodeToMessage($e->getCode()),
            'code' => $e->getCode(),
            'attributes' => null,
        ), $httpStatusCode);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}