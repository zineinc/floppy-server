<?php


namespace ZineInc\Storage\Tests\Server\Stub;


use Symfony\Component\HttpFoundation\Request;
use ZineInc\Storage\Server\RequestHandler\Security\Firewall;

class FirewallStub implements Firewall
{
    public function guard(Request $request, $actionName)
    {
    }
}