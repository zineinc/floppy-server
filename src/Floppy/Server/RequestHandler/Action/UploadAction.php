<?php


namespace Floppy\Server\RequestHandler\Action;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Floppy\Common\ChecksumChecker;
use Floppy\Server\FileHandler\FileHandler;
use Floppy\Common\FileSource;
use Floppy\Server\RequestHandler\FileHandlerNotFoundException;
use Floppy\Server\RequestHandler\FileSourceFactory;
use Floppy\Server\Storage\Storage;

class UploadAction implements Action
{
    private $fileSourceFactory;
    private $storage;
    private $fileHandlers;
    private $checksumChecker;

    public function __construct(Storage $storage, FileSourceFactory $fileSourceFactory, array $fileHandlers, ChecksumChecker $checksumChecker)
    {
        $this->fileHandlers = $fileHandlers;
        $this->fileSourceFactory = $fileSourceFactory;
        $this->storage = $storage;
        $this->checksumChecker = $checksumChecker;
    }

    public function execute(Request $request)
    {
        $fileSource = $this->fileSourceFactory->createFileSource($request);

        $fileHandlerName = $this->findFileHandlerName($fileSource);
        $fileHandler = $this->fileHandlers[$fileHandlerName];

        $fileSource = $fileHandler->beforeStoreProcess($fileSource);
        $attrs = $fileHandler->getStoreAttributes($fileSource);

        $id = $this->storage->store($fileSource);

        $attrs['id'] = $id;
        $attrs['type'] = $fileHandlerName;

        return new JsonResponse(array(
            'code' => 0,
            'attributes' => $attrs + array('checksum' => $this->checksumChecker->generateChecksum($attrs)),
        ));
    }

    /**
     * @return FileHandler
     */
    private function findFileHandlerName(FileSource $fileSource)
    {
        foreach ($this->fileHandlers as $name => $fileHandler) {
            if ($fileHandler->supports($fileSource->fileType())) {
                return $name;
            }
        }

        throw new FileHandlerNotFoundException(sprintf('File type "%s" is unsupported', $fileSource->fileType()->mimeType()));
    }

    /**
     * @return string
     */
    public static function name()
    {
        return 'upload';
    }
}