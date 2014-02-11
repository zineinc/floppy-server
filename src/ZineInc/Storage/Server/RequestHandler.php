<?php

namespace ZineInc\Storage\Server;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface RequestHandler
{
    /**
     * @return Response
     */
    public function handle(Request $request);
}