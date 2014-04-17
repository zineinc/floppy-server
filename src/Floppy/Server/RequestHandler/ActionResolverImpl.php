<?php


namespace Floppy\Server\RequestHandler;


use Floppy\Common\ChecksumChecker;
use Floppy\Server\RequestHandler\Action\Action;
use Floppy\Server\RequestHandler\Action\Exception\ActionNotFoundException;
use Floppy\Server\RequestHandler\Action\CorsEtcAction;
use Floppy\Server\RequestHandler\Action\DownloadAction;
use Floppy\Server\RequestHandler\Action\UploadAction;
use Floppy\Server\Storage\Storage;
use Symfony\Component\HttpFoundation\Request;

class ActionResolverImpl implements ActionResolver
{
    private $actions = array();
    private $matchers = array();

    /**
     * @param Request $request
     * @return Action
     */
    public function resolveAction(Request $request)
    {
        foreach($this->matchers as $actionName => $matcher) {
            if($matcher($request)) {
                return $this->actions[$actionName];
            }
        }

        throw new ActionNotFoundException();
    }

    /**
     * @param Action $action
     * @param $matcher
     * @return ActionResolverImpl
     */
    public function register(Action $action, $matcher)
    {
        $this->matchers[$action->name()] = $matcher;
        $this->actions[$action->name()] = $action;

        return $this;
    }
}