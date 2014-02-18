<?php

namespace ZineInc\Storage\Tests\Server\FileHandler;

use ZineInc\Storage\Server\FileHandler\FileVariantMatcher;
use ZineInc\Storage\Server\FileId;
use ZineInc\Storage\Tests\Server\Stub\ChecksumChecker;

class FileVariantMatcherTest extends AbstractVariantMatcherTest
{
    protected function createVariantMatcher(ChecksumChecker $checksumChecker)
    {
        return new FileVariantMatcher($checksumChecker);
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