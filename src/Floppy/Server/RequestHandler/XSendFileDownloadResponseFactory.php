<?php


namespace Floppy\Server\RequestHandler;


use Floppy\Common\FileSource;
use Symfony\Component\HttpFoundation\Response;

class XSendFileDownloadResponseFactory implements DownloadResponseFactory
{
    private $fallbackFactory;

    public function __construct(DownloadResponseFactory $fallbackFactory = null)
    {
        $this->fallbackFactory = $fallbackFactory ?: new DownloadResponseFactoryImpl();
    }

    public function createResponse(FileSource $fileSource)
    {
        if($fileSource->filepath() !== null) {
            return new Response('', 200, array(
                'X-Sendfile' => $fileSource->filepath(),
                'Content-Type' => $fileSource->fileType()->mimeType(),
            ));
        } else {
            return $this->fallbackFactory->createResponse($fileSource);
        }
    }
}