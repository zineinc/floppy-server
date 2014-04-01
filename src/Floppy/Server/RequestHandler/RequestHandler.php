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
    /**
     * @var LoggerInterface
     */
    private $logger;
    private $firewall;
    private $actionResolver;
    private $responseFilter;

    public function __construct(ActionResolver $actionResolver, Firewall $firewall, ResponseFilter $responseFilter = null)
    {
        $this->logger = new NullLogger();
        $this->firewall = $firewall;
        $this->responseFilter = $responseFilter ?: new NullResponseFilter();

        $this->actionResolver = $actionResolver;
    }

    /**
     * @return Response
     */
    public function handle(Request $request)
    {
        $action = $this->actionResolver->resolveAction($request);

        return $this->executeAction($action, $request);
    }

    private function executeAction(Action $action, Request $request)
    {
        try {
            $this->firewall->guard($request, $action->name());

            $response = $action->execute($request);

            return $this->responseFilter->filterResponse($request, $response);
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