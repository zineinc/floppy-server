<?php


namespace Floppy\Server\RequestHandler\Action;


use Floppy\Server\FileHandler\FileHandlerProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Floppy\Common\ChecksumChecker;
use Floppy\Server\FileHandler\FileHandler;
use Floppy\Common\FileSource;
use Floppy\Server\RequestHandler\Exception\FileHandlerNotFoundException;
use Floppy\Server\RequestHandler\FileSourceFactory;
use Floppy\Server\Storage\Storage;
use Floppy\Server\RequestHandler\Security;

class UploadAction implements Action
{
    private $fileSourceFactory;
    private $storage;
    private $fileHandlerProvider;
    private $checksumChecker;
    private $securityRule;

    public function __construct(Storage $storage, FileSourceFactory $fileSourceFactory, array $fileHandlers, ChecksumChecker $checksumChecker, Security\Rule $securityRule = null)
    {
        $this->fileSourceFactory = $fileSourceFactory;
        $this->storage = $storage;
        $this->checksumChecker = $checksumChecker;
        $this->securityRule = $securityRule ?: new Security\NullRule();
        $this->fileHandlerProvider = new FileHandlerProvider($fileHandlers);
    }

    public function execute(Request $request)
    {
        $fileSource = $this->fileSourceFactory->createFileSource($request);
        $fileSource = $this->securityRule->processRule($request, $fileSource);

        $fileHandlerName = $this->fileHandlerProvider->findFileHandlerName($fileSource);
        $fileHandler = $this->fileHandlerProvider->getFileHandler($fileHandlerName);

        $fileSource = $fileHandler->beforeStoreProcess($fileSource);
        $attrs = $fileHandler->getStoreAttributes($fileSource);

        $id = $this->storage->store($fileSource);

        $attrs['id'] = $id;
        $attrs['type'] = $fileHandlerName;
        $attrs['extra_info'] = $fileSource->info()->all();

        return new JsonResponse(array(
            'code' => 0,
            'attributes' => $attrs + array('checksum' => $this->checksumChecker->generateChecksum($attrs)),
        ));
    }

    /**
     * @return string
     */
    public static function name()
    {
        return 'upload';
    }
}