<?php

namespace ZineInc\Storage\Tests\Common\FileHandler;

use ZineInc\Storage\Common\FileHandler\FileVariantMatcher;
use ZineInc\Storage\Common\FileId;
use ZineInc\Storage\Tests\Common\FileHandler\AbstractVariantMatcherTest;
use ZineInc\Storage\Tests\Common\Stub\ChecksumChecker;

class FileVariantMatcherTest extends AbstractVariantMatcherTest
{
    protected function createVariantMatcher(ChecksumChecker $checksumChecker)
    {
        return new \ZineInc\Storage\Common\FileHandler\FileVariantMatcher($checksumChecker);
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