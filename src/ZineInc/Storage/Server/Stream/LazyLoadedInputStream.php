<?php


namespace ZineInc\Storage\Server\Stream;


use Symfony\Component\Filesystem\Exception\IOException;

class LazyLoadedInputStream extends StringInputStream
{
    private $filepath;

    public function __construct($filepath)
    {
        $this->filepath = $filepath;
    }

    protected function getBytes()
    {
        if($this->bytes === null) {
            $this->bytes = $this->load();
        }

        return $this->bytes;
    }

    private function load()
    {
        $result = @file_get_contents($this->filepath);

        if($result === false) {
            throw new IOException(sprintf('File "%s" can not be loaded.', $this->filepath));
        }

        return $result;
    }

    public function filepath()
    {
        return $this->filepath;
    }
}