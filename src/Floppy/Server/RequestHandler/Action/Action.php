<?php


namespace Floppy\Server\RequestHandler\Action;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface Action
{
    /**
     * @param Request $request
     * @return Response
     */
    public function execute(Request $request);
} 