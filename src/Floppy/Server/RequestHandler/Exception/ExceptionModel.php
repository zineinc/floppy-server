<?php


namespace Floppy\Server\RequestHandler\Exception;


class ExceptionModel
{
    private $httpStatusCode;
    private $message;
    private $messageParameters;

    public function __construct($httpStatusCode, $message, array $messageParameters = array())
    {
        $this->httpStatusCode = (int) $httpStatusCode;
        $this->message = $message;
        $this->messageParameters = $messageParameters;
    }

    public function httpStatusCode()
    {
        return $this->httpStatusCode;
    }

    public function message()
    {
        return $this->message;
    }

    public function messageParameters()
    {
        return $this->messageParameters;
    }
} 