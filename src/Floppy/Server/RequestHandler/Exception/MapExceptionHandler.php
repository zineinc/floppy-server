<?php


namespace Floppy\Server\RequestHandler\Exception;

class MapExceptionHandler implements ExceptionHandler
{
    protected $map = array();

    public function __construct($map)
    {
        $this->map = $map;
    }

    /**
     * @param \Exception $e
     * @return ExceptionModel
     */
    public function handleException(\Exception $e)
    {
        foreach($this->map as $class => $data) {
            list($code, $message) = $data;
            $messageParameters = array();
            if($e instanceof $class) {
                if($e instanceof MessageTemplateException) {
                    $message = $e->getMessageTemplate();
                    $messageParameters = $e->getMessageTemplateParameters();
                }
                return new ExceptionModel($code, $message, $messageParameters);
            }
        }

        return new ExceptionModel(500, 'unknown');
    }
}