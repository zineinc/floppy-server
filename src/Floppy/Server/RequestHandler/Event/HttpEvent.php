<?php


namespace Floppy\Server\RequestHandler\Event;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HttpEvent extends \Symfony\Component\EventDispatcher\Event
{
    private $request;
    private $response;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response = null)
    {
        $this->response = $response;
    }
}