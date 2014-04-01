<?php


namespace Floppy\Server\RequestHandler;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NullResponseFilter implements ResponseFilter
{

    public function filterResponse(Request $request, Response $response)
    {
        return $response;
    }
}