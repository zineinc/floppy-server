<?php


namespace Floppy\Server\RequestHandler\Exception;


class ExceptionModel
{
    private $httpStatusCode;
    private $message;

    public function __construct($httpStatusCode, $message)
    {
        $this->httpStatusCode = (int) $httpStatusCode;
        $this->message = $message;
    }

    public function httpStatusCode()
    {
        return $this->httpStatusCode;
    }

    public function message()
    {
        return $this->message;
    }
} 