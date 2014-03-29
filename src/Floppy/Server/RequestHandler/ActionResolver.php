<?php


namespace Floppy\Server\RequestHandler;


use Floppy\Server\RequestHandler\Action\Action;
use Symfony\Component\HttpFoundation\Request;

interface ActionResolver
{
    /**
     * @param Request $request
     * @return Action
     */
    public function resolveAction(Request $request);
} 