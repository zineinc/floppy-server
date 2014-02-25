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

    /**
     * Gets name of filepath or null when InputStream didn't support filepath
     *
     * @return string|null
     */
    public function filepath();
}