<?php


namespace Floppy\Server\RequestHandler\Exception;


interface MessageTemplateException
{
    public function getMessageTemplate();
    public function getMessageTemplateParameters();
} 