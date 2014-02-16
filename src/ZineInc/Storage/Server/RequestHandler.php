<?php

namespace ZineInc\Storage\Server;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ZineInc\Storage\Server\FileHandler\FileHandler;
use ZineInc\Storage\Server\Storage\Storage;

class RequestHandler
{
//    TODO:
//    private $logger;

    private $storage;
    private $fileSourceFactory;
    private $fileHandlers;

    public function __construct(Storage $storage, FileSourceFactory $fileSourceFactory, array $handlers)
    {
        $this->storage = $storage;
        $this->fileSourceFactory = $fileSourceFactory;
        $this->fileHandlers = $handlers;
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
        //TODO: handle FileSourceNotFoundEx
        $fileSource = $this->fileSourceFactory->createFileSource($request);

        $fileHandler = $this->findFileHandler($fileSource);

        if($fileHandler === null) {
            //TODO: return response with 40x code
        }

        $fileSource = $fileHandler->beforeStoreProcess($fileSource);
        $attrs = $fileHandler->getStoreAttributes($fileSource);

        //TODO: handle StoreException
        $id = $this->storage->store($fileSource);
        
        $attrs['id'] = $id;

        return new JsonResponse($attrs);
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

        return null;
    }
}