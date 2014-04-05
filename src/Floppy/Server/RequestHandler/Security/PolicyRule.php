<?php


namespace Floppy\Server\RequestHandler\Security;


use Floppy\Common\FileId;
use Floppy\Common\FileSource;
use Symfony\Component\HttpFoundation\Request;
use Floppy\Common\ChecksumChecker;
use Floppy\Server\RequestHandler\AccessDeniedException;

class PolicyRule implements Rule
{
    private $checksumChecker;

    public function __construct(ChecksumChecker $checksumChecker)
    {
        $this->checksumChecker = $checksumChecker;
    }

    public function checkRule(Request $request, $object = null)
    {
        $policy = $request->request->get('policy');
        $signature = $request->request->get('signature');

        if(!$policy || !$signature || !$this->checksumChecker->isChecksumValid($signature, $policy)) {
            throw new AccessDeniedException();
        }

        $decodedPolicy = @json_decode(base64_decode($policy), true);

        if($decodedPolicy === false) {
            throw new AccessDeniedException('Invalid policy format, deserialization failed');
        }

        if(!empty($decodedPolicy['expiration']) && $decodedPolicy['expiration'] < time()) {
            throw new AccessDeniedException('Policy is expired');
        }
    }
}