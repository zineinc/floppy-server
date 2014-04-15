<?php


namespace Floppy\Server\RequestHandler\Security;


use Symfony\Component\HttpFoundation\Request;

class CallbackFirewall implements Firewall
{
    private $callables = array();

    public function __construct(array $callbacks) {
        foreach($callbacks as $actionName => $callback) {
            if(!is_callable($callback)) {
                throw new \InvalidArgumentException(sprintf('$callback should be callable, "%s" given', is_object($callback) ? get_class($callback) : gettype($callback)));
            }

            $this->callables[$actionName] = $callback;
        }
    }

    public function guard(Request $request, $actionName)
    {
        if(isset($this->callables[$actionName])) {
            call_user_func($this->callables[$actionName], $request);
        }
    }
}