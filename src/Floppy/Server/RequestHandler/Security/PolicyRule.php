<?php


namespace Floppy\Server\RequestHandler\Security;


use Floppy\Common\FileId;
use Floppy\Common\FileSource;
use Floppy\Common\HasFileInfo;
use Floppy\Server\FileHandler\FileHandlerProvider;
use Floppy\Server\RequestHandler\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Floppy\Common\ChecksumChecker;
use Floppy\Server\RequestHandler\Exception\AccessDeniedException;

class PolicyRule implements Rule
{
    private $checksumChecker;
    private $fileHandlerProvider;

    public function __construct(ChecksumChecker $checksumChecker, array $fileHandlers)
    {
        $this->checksumChecker = $checksumChecker;
        $this->fileHandlerProvider = new FileHandlerProvider($fileHandlers);
    }

    public function processRule(Request $request, HasFileInfo $object)
    {
        $policy = $this->retrievePolicy($request);
        $this->checkExpiration($policy);
        $object = $this->checkFileType($policy, $object);
        $object = $this->checkFileId($policy, $object);
        $object = $this->processAccessType($policy, $object);

        return $object;
    }

    protected function retrievePolicy(Request $request)
    {
        $policy = $request->get('policy');
        $signature = $request->get('signature');

        if (!$policy || !$signature || !$this->checksumChecker->isChecksumValid($signature, $policy)) {
            throw new AccessDeniedException();
        }

        $decodedPolicy = @json_decode(base64_decode($policy), true);

        if ($decodedPolicy === false) {
            throw new AccessDeniedException('Invalid policy format, deserialization failed');
        }

        return $decodedPolicy;
    }

    protected function checkExpiration($policy)
    {
        if (!empty($policy['expiration']) && $policy['expiration'] < time()) {
            throw new AccessDeniedException('Policy is expired');
        }
    }

    protected function checkFileType($policy, HasFileInfo $object)
    {
        if($object instanceof FileSource && !empty($policy['file_types'])) {
            $fileHandlerName = $this->fileHandlerProvider->findFileHandlerName($object);
            $fileTypes = (array) $policy['file_types'];

            if(!in_array($fileHandlerName, $fileTypes)) {
                throw new ValidationException('Invalid file type, given "%current_file_type%", allowed %allowed_file_types%', array('%current_file_type%' => $fileHandlerName, '%allowed_file_types%' => implode(', ', $fileTypes)));
            }
        }

        return $object;
    }

    protected function checkFileId($policy, HasFileInfo $object)
    {
        if($object instanceof FileId && (empty($policy['id']) || $policy['id'] !== $object->id())) {
            $id = isset($policy['id']) ? $policy['id'] : '(empty)';
            throw new AccessDeniedException(sprintf('Wrong policy id, expected "%s", "%s" given', $object->id(), $id));
        }

        return $object;
    }

    protected function processAccessType($policy, HasFileInfo $object)
    {
        if(isset($policy['access'])) {
            $object = $object->withInfo(array('access' => $policy['access']) + $object->info()->all());
        }

        return $object;
    }
}