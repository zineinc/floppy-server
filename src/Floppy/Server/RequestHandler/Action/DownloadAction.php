<?php


namespace Floppy\Server\RequestHandler\Action;


use Floppy\Server\FileHandler\FileHandlerProvider;
use Symfony\Component\HttpFoundation\Request;
use Floppy\Common\ErrorCodes;
use Floppy\Server\FileHandler\FileHandler;
use Floppy\Server\RequestHandler\Action\Action;
use Floppy\Server\RequestHandler\DownloadResponseFactory;
use Floppy\Server\RequestHandler\FileHandlerNotFoundException;
use Floppy\Server\Storage\Storage;
use Floppy\Server\RequestHandler\Security;

class DownloadAction implements Action
{
    private $fileHandlerProvider;
    private $storage;
    private $downloadResponseFactory;
    private $securityRule;

    function __construct(Storage $storage, DownloadResponseFactory $downloadResponseFactory, array $fileHandlers, Security\Rule $securityRule = null)
    {
        $this->downloadResponseFactory = $downloadResponseFactory;
        $this->fileHandlerProvider = new FileHandlerProvider($fileHandlers);
        $this->storage = $storage;
        $this->securityRule = $securityRule ?: new Security\NullRule();
    }

    public function execute(Request $request)
    {
        $path = rtrim($request->getPathInfo(), '/').($request->getQueryString() ? '?'.$request->getQueryString() : '');
        $handlerName = $this->fileHandlerProvider->findFileHandlerNameMatches($path);
        $handler = $this->fileHandlerProvider->getFileHandler($handlerName);

        $fileId = $handler->match($path);

        $this->securityRule->checkRule($request, $fileId);

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
     * @return string
     */
    public static function name()
    {
        return 'download';
    }
}