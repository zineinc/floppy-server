<?php

namespace ZineInc\Storage\Tests\Common\Stub;

use ZineInc\Storage\Common\ChecksumChecker as ChecksumCheckerInterface;

class ChecksumChecker implements ChecksumCheckerInterface
{
    private $validChecksum;

    public function __construct($validChecksum)
    {
        $this->validChecksum = $validChecksum;
    }

    public function isChecksumValid($checksum, $data)
    {
        return $checksum == $this->validChecksum;
    }
}