<?php

namespace Floppy\Server\RequestHandler;

use Floppy\Server\RequestHandler\Action\CorsEtcAction;
use Floppy\Server\RequestHandler\Exception\DefaultMapExceptionHandler;
use Floppy\Server\RequestHandler\Exception\ExceptionHandler;
use Floppy\Server\RequestHandler\Exception\ExceptionModel;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Floppy\Common\ChecksumChecker;
use Floppy\Common\FileHandler\PathMatchingException;
use Floppy\Server\FileHandler\FileHandler;
use Floppy\Common\FileSource;
use Floppy\Server\RequestHandler\Security\Firewall;
use Floppy\Server\Storage\Storage;
use Floppy\Common\Exception\StorageError;
use Floppy\Common\Exception\StorageException;
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
    private $exceptionHandler;

    public function __construct(ActionResolver $actionResolver, Firewall $firewall, ResponseFilter $responseFilter = null, ExceptionHandler $exceptionHandler = null)
    {
        $this->logger = new NullLogger();
        $this->firewall = $firewall;
        $this->responseFilter = $responseFilter ?: new NullResponseFilter();
        $this->exceptionHandler = $exceptionHandler ?: new DefaultMapExceptionHandler();

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
        } catch (\Exception $e) {
            $exceptionModel = $this->exceptionHandler->handleException($e);
            if($exceptionModel->httpStatusCode() >= 500) {
                $this->logger->error($e);
            } else {
                $this->logger->warning($e);
            }
            return $this->createErrorResponse($exceptionModel);
        }
    }

    private function createErrorResponse(ExceptionModel $exceptionModel)
    {
        return new JsonResponse(array(
            'message' => $exceptionModel->message(),
            'messageParameters' => $exceptionModel->messageParameters(),
            'attributes' => null,
        ), $exceptionModel->httpStatusCode());
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}