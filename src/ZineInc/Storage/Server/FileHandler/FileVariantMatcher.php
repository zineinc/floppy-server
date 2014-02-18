<?php

namespace ZineInc\Storage\Server\FileHandler;

use ZineInc\Storage\Server\FileId;

class FileVariantMatcher implements VariantMatcher
{
    private $checksumChecker;

    public function __construct(ChecksumChecker $checksumChecker)
    {
        $this->checksumChecker = $checksumChecker;
    }

    public function match($variantFilepath)
    {
        $variantFilepath = basename($variantFilepath);

        $parsedUrl = parse_url($variantFilepath);

        $path = $parsedUrl['path'];
        $query = $this->parseQuery($parsedUrl['query']);

        $name = isset($query['name']) ? $query['name'] : null;
        $checksum = isset($query['checksum']) ? $query['checksum'] : null;

        if(!$this->checksumChecker->isChecksumValid($checksum, array($path, $name))) {
            throw new VariantMatchingException();
        }

        return new FileId($path, array(
            'name' => $name,
        ));
    }

    private function parseQuery($query)
    {
        $result = array();

        foreach(explode('&', $query) as $value) {
            list($name, $value) = explode('=', $value);
            $result[$name] = $value;
        }

        return $result;
    }
}