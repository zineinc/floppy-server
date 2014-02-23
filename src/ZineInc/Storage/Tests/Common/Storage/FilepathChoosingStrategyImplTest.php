<?php

namespace ZineInc\Storage\Tests\Common\Storage;

use PHPUnit_Framework_TestCase;
use ZineInc\Storage\Common\FileId;
use ZineInc\Storage\Common\Storage\FilepathChoosingStrategyImpl;

class FilepathChoosingStrategyImplTest extends PHPUnit_Framework_TestCase
{
    const CHARS_FOR_DIR = 3;
    const DIRS_COUNT = 2;
    const ORIG_DIR = 'o';
    const VARIANT_DIR = 'v';

    private $strategy;

    protected function setUp()
    {
        $this->strategy = new \ZineInc\Storage\Common\Storage\FilepathChoosingStrategyImpl(self::DIRS_COUNT, self::CHARS_FOR_DIR, self::ORIG_DIR, self::VARIANT_DIR);
    }

    /**
     * @test
     * @dataProvider fileIdProvider
     */
    public function testFilepath(FileId $fileId, $expectedFilepath)
    {
        $actualFilepath = $this->strategy->filepath($fileId);

        $this->assertEquals($expectedFilepath, $actualFilepath);
    }

    public function fileIdProvider()
    {
        return array(
            array(new FileId('abcdefghijk'), self::ORIG_DIR . '/abc/def'),
            array(new FileId('abcdefghijk', array('someOption' => 'value')), self::VARIANT_DIR . '/abc/def'),
        );
    }
}