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
            if($e instanceof $class) {
                return new ExceptionModel($code, $message);
            }
        }

        return new ExceptionModel(500, 'unknown');
    }
}