<?php

namespace ZineInc\Storage\Tests\Server\FileHandler;

use ZineInc\Storage\Server\FileHandler\ImageVariantMatcher;
use ZineInc\Storage\Server\FileId;
use ZineInc\Storage\Tests\Server\Stub\ChecksumChecker;

class ImageVariantMatcherTest extends AbstractVariantMatcherTest
{
    protected function createVariantMatcher(ChecksumChecker $checksumChecker)
    {
        return new ImageVariantMatcher($checksumChecker);
    }

    public function dataProvider()
    {
        return array(
            array(
                'some/dirs/to/ignore/'.self::VALID_CHECKSUM.'_900_502_ffffff_0_0_0_0_fileid.jpeg',
                false,
                new FileId('fileid.jpeg', array(
                    'width' => 900,
                    'height' => 502,
                    'cropBackgroundColor' => 'ffffff',
                    'crop' => array(0, 0, 0, 0)
                ))
            ),
            array(
                'some/dirs/to/ignore/'.self::INVALID_CHECKSUM.'_900_502_ffffff_0_0_0_0_fileid.jpeg',
                true,
                null,
            ),
            array(
                'some/dirs/to/ignore/'.self::VALID_CHECKSUM.'_0_0_0_fileid.jpeg',
                true,
                null,
            ),
        );
    }
}