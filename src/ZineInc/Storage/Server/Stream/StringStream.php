<?php

namespace ZineInc\Storage\Server\Stream;

class StringStream implements Stream
{
    private $bytes;
    private $readPointer = 0;
    private $writePointer = 0;
    private $closed = false;

    public function __construct($buffer = '')
    {
        $this->bytes = (string) $buffer;
    }

    public function isReadable()
    {
        return true;
    }

    public function isWritable()
    {
        return true;
    }

    public function close()
    {
        $this->closed = true;
    }

    public function read($len = null)
    {
        $this->ensureOpened();

        if($this->readPointer === 0 && $len === null)
        {
            $this->readPointer = strlen($this->bytes);
            return $this->bytes;
        }

        $len = $len === null ? (strlen($this->bytes) - $this->readPointer) : $len;
        $bytes = substr($this->bytes, $this->readPointer, $len);

        if($bytes === false)
        {
            $bytes = null;
        }
        else
        {
            $this->readPointer += strlen($bytes);
        }

        return $bytes;
    }

    private function ensureOpened()
    {
        if($this->closed)
        {
            throw new IOException('Stream is closed');
        }
    }

    public function resetInput()
    {
        $this->readPointer = 0;
    }

    public function resetOutput()
    {
        $this->writePointer = 0;
    }

    public function write($bytes)
    {
        $this->ensureOpened();

        $len = strlen($this->bytes);
        $appendBytesLength = strlen($bytes);

        if($len <= $this->writePointer)
        {
            $this->bytes .= $bytes;
        }
        else
        {
            for($i=$this->writePointer, $j=0; $i<$len && $j<$appendBytesLength; $i++, $j++)
            {
                $this->bytes[$i] = $bytes[$j];
            }

            if($j < $appendBytesLength - 1)
            {
                $this->bytes .= substr($bytes, $j);
            }
        }

        $this->writePointer += $appendBytesLength;

        return $appendBytesLength;
    }
}