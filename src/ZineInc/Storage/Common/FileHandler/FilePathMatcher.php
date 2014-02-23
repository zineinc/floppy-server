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

        $path = $parsedUrl['path'];
        $query = $this->parseQuery($parsedUrl['query']);

        $name = isset($query['name']) ? $query['name'] : null;
        $checksum = isset($query['checksum']) ? $query['checksum'] : null;

        if (!$name || !$this->checksumChecker->isChecksumValid($checksum, array($path, $name))) {
            throw new PathMatchingException();
        }

        return new FileId($path, array(
            'name' => $name,
        ));
    }

    private function parseQuery($query)
    {
        $result = array();

        foreach (explode('&', $query) as $value) {
            list($name, $value) = explode('=', $value);
            $result[$name] = $value;
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
        $query = $this->parseQuery($parsedUrl['query']);

        return isset($query['name'], $query['checksum']);
    }
}