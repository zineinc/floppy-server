<?php

namespace ZineInc\Storage\Tests\Common\FileHandler;

use PHPUnit_Framework_TestCase;
use ZineInc\Storage\Tests\Common\Stub\ChecksumChecker;

abstract class AbstractPathMatcherTest extends PHPUnit_Framework_TestCase
{
    const VALID_CHECKSUM = 'validChecksum';
    const INVALID_CHECKSUM = 'invalidChecksum';

    private $matcher;

    protected function setUp()
    {
        $this->matcher = $this->createVariantMatcher(new ChecksumChecker(self::VALID_CHECKSUM));
    }

    protected abstract function createVariantMatcher(ChecksumChecker $checksumChecker);

    /**
     * @test
     * @dataProvider matchDataProvider
     */
    public function testMatch($variantFilename, $expectedException, $expectedFileId)
    {
        if ($expectedException) {
            $this->setExpectedException('ZineInc\Storage\Common\FileHandler\PathMatchingException');
        }

        $actualFileId = $this->matcher->match($variantFilename);

        $this->assertEquals($expectedFileId, $actualFileId);
    }

    public abstract function matchDataProvider();

    /**
     * @test
     * @dataProvider matchesDataProvider
     */
    public function testMatches($variantFilename, $expectedMatches)
    {
        $this->assertEquals($expectedMatches, $this->matcher->matches($variantFilename));
    }

    public abstract function matchesDataProvider();

    /**
     * @test
     * @dataProvider matchesDataProvider
     */
    public function testMatch_throwExceptionWhenMatchesReturnFalse($variantFilepath, $expectedMatches)
    {
        if ($expectedMatches) {
            //skip, this tests only condition when PathMatcher::matches return false
            return;
        }

        if (!$expectedMatches) {
            $this->setExpectedException('ZineInc\Storage\Common\FileHandler\PathMatchingException');
        }

        $this->matcher->match($variantFilepath);
    }
}