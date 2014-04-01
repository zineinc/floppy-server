<?php


namespace Floppy\Server\RequestHandler;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ResponseFilter
{
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function filterResponse(Request $request, Response $response);
} 