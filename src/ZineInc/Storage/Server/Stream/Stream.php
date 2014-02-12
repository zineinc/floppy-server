<?php

namespace ZineInc\Storage\Server\Stream;

interface Stream extends InputStream, OutputStream
{
    public function isReadable();
    public function isWritable();
}