<?php


namespace Floppy\Server\RequestHandler\Exception;


use Exception;
use Floppy\Common\Exception\StorageException;

class ValidationException extends \Exception implements StorageException, MessageTemplateException
{
    private $messageTemplate;
    private $messageTemplateParameters;

    public function __construct($messageTemplate, array $messageTemplateParameters = array())
    {
        parent::__construct(strtr($messageTemplate, $messageTemplateParameters));

        $this->messageTemplate = $messageTemplate;
        $this->messageTemplateParameters = $messageTemplateParameters;
    }


    public function getMessageTemplate()
    {
        return $this->messageTemplate;
    }

    public function getMessageTemplateParameters()
    {
        return $this->messageTemplateParameters;
    }
}