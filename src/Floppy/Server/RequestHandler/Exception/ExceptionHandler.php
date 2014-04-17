<?php


namespace Floppy\Server\RequestHandler\Exception;


use Floppy\Server\RequestHandler\Exception\ExceptionModel;

interface ExceptionHandler
{
    /**
     * @param \Exception $e
     * @return ExceptionModel
     */
    public function handleException(\Exception $e);
} 