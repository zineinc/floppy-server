<?php

namespace ZineInc\Storage\Server\Stream;

interface OutputStream
{
    /**
     * @throws IOException
     */
    public function close();

    /**
     * @param string $bytes Bytes to write to stream
     * @return int Number of writed bytes
     *
     * @throws IOException
     */
    public function write($bytes);

    public function resetOutput();
}