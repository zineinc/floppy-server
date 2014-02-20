<?php

namespace ZineInc\Storage\Tests\Common\FileHandler;

use ZineInc\Storage\Common\FileHandler\FilePathMatcher;
use ZineInc\Storage\Common\FileId;
use ZineInc\Storage\Tests\Common\FileHandler\AbstractPathMatcherTest;
use ZineInc\Storage\Tests\Common\Stub\ChecksumChecker;

class FilePathMatcherTest extends AbstractPathMatcherTest
{
    protected function createVariantMatcher(ChecksumChecker $checksumChecker)
    {
        return new \ZineInc\Storage\Common\FileHandler\FilePathMatcher($checksumChecker);
    }

    public function dataProvider()
    {
        return array(
            array(
                'some/dirs/to/ignore/fileid.zip?name=some-name&checksum='.self::VALID_CHECKSUM,
                false,
                new FileId('fileid.zip', array(
                    'name' => 'some-name',
                ))
            ),
            array(
                'some/dirs/to/ignore/fileid.zip?name=some-name&checksum='.self::INVALID_CHECKSUM,
                true,
                null,
            ),
        );
    }
}