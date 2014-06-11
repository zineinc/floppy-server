<?php


namespace Floppy\Tests\Server\Stub;


use Floppy\Common\HasFileInfo;
use Floppy\Server\RequestHandler\Security\Rule;
use Symfony\Component\HttpFoundation\Request;

class SecurityRuleStub implements Rule
{
    private $info;

    public function __construct(array $info)
    {
        $this->info = $info;
    }

    public function processRule(Request $request, HasFileInfo $object)
    {
        return $object->withInfo($this->info);
    }
}