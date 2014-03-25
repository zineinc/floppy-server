<?php


namespace Floppy\Server\RequestHandler\Action;


use Symfony\Component\HttpFoundation\Request;

interface Action
{
    public function execute(Request $request);
} 