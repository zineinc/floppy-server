<?php

namespace ZineInc\Storage\Server\FileHandler;

interface ChecksumChecker
{
    /**
     * @return boolean
     */
    public function isChecksumValid($checksum, $data);
}