<?php


namespace Floppy\Tests\Server\Stub;


use Symfony\Component\HttpFoundation\Request;
use Floppy\Server\RequestHandler\Security\Firewall;

class FirewallStub implements Firewall
{
    public function guard(Request $request, $actionName)
    {
    }
}