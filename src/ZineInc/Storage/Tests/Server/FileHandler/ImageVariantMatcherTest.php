<?php

namespace ZineInc\Storage\Tests\Server\FileHandler;

use PHPUnit_Framework_TestCase;
use ZineInc\Storage\Server\FileHandler\ChecksumChecker;
use ZineInc\Storage\Server\FileHandler\ImageVariantMatcher;
use ZineInc\Storage\Server\FileId;

class ImageVariantMatcherTest extends PHPUnit_Framework_TestCase
{
    const VALID_CHECKSUM = 'validChecksum';
    const INVALID_CHECKSUM = 'invalidChecksum';

    private $matcher;

    protected function setUp()
    {
        $this->matcher = new ImageVariantMatcher(new ImageVariantMatcherTest_ChecksumChecker(self::VALID_CHECKSUM));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function testMatch($variantFilename, $expectedException, $expectedFileId)
    {
        if($expectedException)
        {
            $this->setExpectedException('ZineInc\Storage\Server\FileHandler\VariantMatchingException');
        }

        $actualFileId = $this->matcher->match($variantFilename);

        $this->assertEquals($expectedFileId, $actualFileId);
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

class ImageVariantMatcherTest_ChecksumChecker implements ChecksumChecker
{
    private $validChecksum;

    public function __construct($validChecksum)
    {
        $this->validChecksum = $validChecksum;
    }

    public function isChecksumValid($checksum, $data)
    {
        return $checksum == $this->validChecksum;
    }
}