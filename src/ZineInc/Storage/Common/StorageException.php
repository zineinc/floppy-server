<?php

namespace ZineInc\Storage\Common;

interface StorageException
{
    public function getMessage();

    public function getCode();
}