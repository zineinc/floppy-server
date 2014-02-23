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

    public function matchDataProvider()
    {
        return array(
            array(
                'some/dirs/to/ignore/fileid.zip?name=some-name&checksum=' . self::VALID_CHECKSUM,
                false,
                new FileId('fileid.zip', array(
                    'name' => 'some-name',
                ), 'fileid.zip')
            ),
            array(
                'some/dirs/to/ignore/fileid.zip?name=some-name&checksum=' . self::INVALID_CHECKSUM,
                true,
                null,
            ),
            array(
                'some/dirs/to/ignore/fileid.zip',
                true,
                null,
            ),
            array(
                'some/dirs/to/ignore/fileid.zip?name',
                true,
                null,
            ),
        );
    }

    public function matchesDataProvider()
    {

        return array(
            array(
                'some/dirs/to/ignore/file.zip?name=some&checksum=' . self::INVALID_CHECKSUM,
                true
            ),
            //checksum is missing
            array(
                'some/dir/to/ignore/file.zip?name=some',
                false
            ),
            //name is missing
            array(
                'some/dir/to/ignore/file.zip?checksum=some',
                false
            ),
        );
    }
}