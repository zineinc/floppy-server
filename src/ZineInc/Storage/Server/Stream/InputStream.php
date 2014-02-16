<?php

namespace ZineInc\Storage\Server\Stream;

interface InputStream
{
    /**
     * @throws IOException
     */
    public function close();

    /**
     * @return string
     * @throws IOException
     */
    public function read();
}