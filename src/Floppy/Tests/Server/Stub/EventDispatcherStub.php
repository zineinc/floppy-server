<?php


namespace Floppy\Tests\Server\Stub;


use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventDispatcherStub implements EventDispatcherInterface
{
    private $callback;

    public function dispatch($eventName, Event $event = null)
    {
        if($this->callback) {
            call_user_func_array($this->callback, func_get_args());
        }
    }

    public function setDispatchCallback(\Closure $callback)
    {
        $this->callback = $callback;
    }

    public function addListener($eventName, $listener, $priority = 0)
    {
    }

    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
    }

    public function removeListener($eventName, $listener)
    {
    }

    public function removeSubscriber(EventSubscriberInterface $subscriber)
    {
    }

    public function getListeners($eventName = null)
    {
    }

    public function hasListeners($eventName = null)
    {
    }
}