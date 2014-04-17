<?php


namespace Floppy\Server\RequestHandler\Security;


use Symfony\Component\HttpFoundation\Request;
use Floppy\Server\RequestHandler\Exception\AccessDeniedException;

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