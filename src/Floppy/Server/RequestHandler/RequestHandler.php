<?php

namespace Floppy\Server\RequestHandler;

use Floppy\Server\RequestHandler\Action\CorsEtcAction;
use Floppy\Server\RequestHandler\Event\Events;
use Floppy\Server\RequestHandler\Event\HttpEvent;
use Floppy\Server\RequestHandler\Exception\DefaultMapExceptionHandler;
use Floppy\Server\RequestHandler\Exception\ExceptionHandler;
use Floppy\Server\RequestHandler\Exception\ExceptionModel;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
    private $exceptionHandler;
    private $eventDispatcher;

    public function __construct(ActionResolver $actionResolver, Firewall $firewall, EventDispatcherInterface $eventDispatcher, ExceptionHandler $exceptionHandler = null)
    {
        $this->logger = new NullLogger();
        $this->firewall = $firewall;
        $this->exceptionHandler = $exceptionHandler ?: new DefaultMapExceptionHandler();

        $this->actionResolver = $actionResolver;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return Response
     */
    public function handle(Request $request)
    {
        if($response = $this->triggerRequestEvent($request)) {
            return $response;
        }

        $action = $this->actionResolver->resolveAction($request);

        $response = $this->executeAction($action, $request);

        $this->triggerResponseEvent($request, $response);

        return $response;
    }

    private function triggerRequestEvent(Request $request)
    {
        $event = new HttpEvent($request);
        $this->eventDispatcher->dispatch(Events::HTTP_REQUEST, $event);

        return $event->getResponse();
    }

    private function triggerResponseEvent(Request $request, Response $response)
    {
        $this->eventDispatcher->dispatch(Events::HTTP_RESPONSE, new HttpEvent($request, $response));
    }

    private function executeAction(Action $action, Request $request)
    {
        try {
            $this->firewall->guard($request, $action->name());

            return $action->execute($request);
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