<?php

namespace ZineInc\Storage\Common;

interface ChecksumChecker
{
    /**
     * @return boolean
     */
    public function isChecksumValid($checksum, $data);
}