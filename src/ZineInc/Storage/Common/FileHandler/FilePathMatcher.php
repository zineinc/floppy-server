<?php

namespace ZineInc\Storage\Common\FileHandler;

use ZineInc\Storage\Common\FileId;
use ZineInc\Storage\Common\ChecksumChecker;

class FilePathMatcher implements PathMatcher
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

        $filename = $parsedUrl['path'];
        $query = isset($parsedUrl['query']) ? $this->parseQuery($parsedUrl['query']) : array();

        $name = isset($query['name']) ? $query['name'] : null;
        $checksum = isset($query['checksum']) ? $query['checksum'] : null;

        if (!$name || !$this->checksumChecker->isChecksumValid($checksum, array($filename, $name))) {
            throw new PathMatchingException();
        }

        return new FileId($filename, array(
            'name' => $name,
        ), $filename);
    }

    private function parseQuery($query)
    {
        $result = array();

        foreach (explode('&', $query) as $value) {
            $parts = explode('=', $value);
            if (count($parts) >= 2) {
                list($name, $value) = $parts;
                $result[$name] = $value;
            }
        }

        return $result;
    }

    /**
     * @param $variantFilepath
     *
     * @return boolean
     */
    public function matches($variantFilepath)
    {
        $parsedUrl = parse_url($variantFilepath);
        $query = isset($parsedUrl['query']) ? $this->parseQuery($parsedUrl['query']) : array();

        return isset($query['name'], $query['checksum']);
    }
}