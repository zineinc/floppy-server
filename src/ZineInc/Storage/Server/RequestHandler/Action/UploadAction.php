<?php


namespace ZineInc\Storage\Server\RequestHandler\Action;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ZineInc\Storage\Server\FileHandler\FileHandler;
use ZineInc\Storage\Common\FileSource;
use ZineInc\Storage\Server\RequestHandler\FileHandlerNotFoundException;
use ZineInc\Storage\Server\RequestHandler\FileSourceFactory;
use ZineInc\Storage\Server\Storage\Storage;

class UploadAction implements Action
{
    private $fileSourceFactory;
    private $storage;
    private $fileHandlers;

    public function __construct(Storage $storage, FileSourceFactory $fileSourceFactory, array $fileHandlers)
    {
        $this->fileHandlers = $fileHandlers;
        $this->fileSourceFactory = $fileSourceFactory;
        $this->storage = $storage;
    }

    public function execute(Request $request)
    {
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
}