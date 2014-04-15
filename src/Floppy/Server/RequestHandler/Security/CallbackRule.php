<?php


namespace Floppy\Server\RequestHandler\Security;


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

    public function checkRule(Request $request, $object = null)
    {
        call_user_func($this->callback, $request, $object);
    }
}