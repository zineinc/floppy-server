<?php

namespace ZineInc\Storage\Tests\Server\Stub;

use ZineInc\Storage\Server\FileHandler\ChecksumChecker as ChecksumCheckerInterface;

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