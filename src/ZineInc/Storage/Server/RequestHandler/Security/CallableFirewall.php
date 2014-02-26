<?php


namespace ZineInc\Storage\Server\RequestHandler\Security;


use Symfony\Component\HttpFoundation\Request;

class CallableFirewall implements Firewall
{
    private $callables = array();

    public function __construct(array $callables) {
        foreach($callables as $actionName => $callable) {
            if(!is_callable($callable)) {
                throw new \InvalidArgumentException(sprintf('$callable should be callable, "%s" given', is_object($callable) ? get_class($callable) : gettype($callable)));
            }

            $this->callables[$actionName] = $callable;
        }
    }

    public function guard(Request $request, $actionName)
    {
        if(isset($this->callables[$actionName])) {
            call_user_func($this->callables[$actionName], $request);
        }
    }
}