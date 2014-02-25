<?php

namespace ZineInc\Storage\Server\Stream;

class StringInputStream implements InputStream
{
    protected $bytes;
    private $closed = false;

    public function __construct($buffer)
    {
        $this->bytes = (string)$buffer;
    }

    public function close()
    {
        $this->closed = true;
    }

    public function read()
    {
        $this->ensureOpened();

        return $this->getBytes();
    }

    protected function getBytes()
    {
        return $this->bytes;
    }

    private function ensureOpened()
    {
        if ($this->closed) {
            throw new IOException('Stream is closed');
        }
    }

    public function filepath()
    {
        return null;
    }
}