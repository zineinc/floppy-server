<?php

namespace ZineInc\Storage\Common;

class ChecksumCheckerImpl implements ChecksumChecker
{
    private $secretKey;
    private $checksumLength;

    public function __construct($secretKey, $checksumLength = 5)
    {
        $this->secretKey = $secretKey;
        $this->checksumLength = (int)$checksumLength;
    }

    public function isChecksumValid($checksum, $data)
    {
        //TODO: safe string comparison?
        return $checksum === substr(md5(serialize($data) . $this->secretKey), 0, $this->checksumLength);
    }
}