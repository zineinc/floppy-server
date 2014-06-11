<?php


namespace Floppy\Server\RequestHandler\Security;


use Floppy\Common\HasFileInfo;
use Symfony\Component\HttpFoundation\Request;

class CallbackRule implements Rule
{
    private $callback;

    public function __construct($callback)
    {
        if(!is_callable($callback)) {
            throw new \InvalidArgumentException('To CallbackRule should be passed valid callable.');
        }

        $this->callback = $callback;
    }

    public function processRule(Request $request, HasFileInfo $object)
    {
        return call_user_func($this->callback, $request, $object);
    }
}