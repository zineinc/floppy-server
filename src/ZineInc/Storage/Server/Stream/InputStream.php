<?php

namespace ZineInc\Storage\Server\Stream;

interface InputStream
{
    /**
     * @throws IOException
     */
    public function close();

    /**
     * @param $len int Number of bytes to read
     */
    public function read($len = null);

    public function resetInput();
}