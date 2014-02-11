<?php

namespace ZineInc\Storage\Server;

interface AttributesBag
{
    public function get($name);
    public function all();
}