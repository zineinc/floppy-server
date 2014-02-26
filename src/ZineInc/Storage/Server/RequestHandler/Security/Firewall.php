<?php


namespace ZineInc\Storage\Server\RequestHandler\Security;


use Symfony\Component\HttpFoundation\Request;
use ZineInc\Storage\Server\RequestHandler\AccessDeniedException;

interface Firewall
{
    /**
     * @param Request $request
     * @param $actionName
     *
     * @return void
     * @throws AccessDeniedException
     */
    public function guard(Request $request, $actionName);
} 