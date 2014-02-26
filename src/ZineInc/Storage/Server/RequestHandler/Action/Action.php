<?php


namespace ZineInc\Storage\Server\RequestHandler\Action;


use Symfony\Component\HttpFoundation\Request;

interface Action
{
    public function execute(Request $request);
} 