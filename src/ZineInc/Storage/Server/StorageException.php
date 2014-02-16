<?php

namespace ZineInc\Storage\Server;

interface StorageException
{
    public function getMessage();
    public function getCode();
}