<?php

namespace ZineInc\Storage\Common\FileHandler;

use ZineInc\Storage\Common\FileHandler\VariantMatcher;
use ZineInc\Storage\Common\FileHandler\VariantMatchingException;
use ZineInc\Storage\Common\FileId;
use ZineInc\Storage\Common\ChecksumChecker;

class ImageVariantMatcher implements VariantMatcher
{
    private $checksumChecker;

    public function __construct(ChecksumChecker $checksumChecker)
    {
        $this->checksumChecker = $checksumChecker;
    }

    public function match($variantFilepath)
    {
        $variantFilepath = basename($variantFilepath);

        $params = explode('_', $variantFilepath);

        if(count($params) !== 9)
        {
            throw new VariantMatchingException(sprintf('Invalid variant filepath format, given: "%s"', $variantFilepath));
        }

        $checksum = array_shift($params);

        if(!$this->checksumChecker->isChecksumValid($checksum, $params))
        {
            throw new VariantMatchingException(sprintf('checksum is invalid for variant: "%s"', $variantFilepath));
        }

        $id = array_pop($params);

        return new FileId($id, array(
            'width' => (int)$params[0],
            'height' => (int)$params[1],
            'cropBackgroundColor' => $params[2],
            'crop' => array((float)$params[3], (float)$params[4], (float)$params[5], (float)$params[6]),
        ));
    }
}