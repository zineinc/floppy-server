<?php

namespace ZineInc\Storage\Tests\Server\Storage;

use PHPUnit_Framework_TestCase;
use ZineInc\Storage\Common\FileId;
use ZineInc\Storage\Server\Storage\FilepathChoosingStrategyImpl;

class FilepathChoosingStrategyImplTest extends PHPUnit_Framework_TestCase
{
    const CHARS_FOR_DIR = 3;
    const DIRS_COUNT = 2;

    private $strategy;

    protected function setUp()
    {
        $this->strategy = new FilepathChoosingStrategyImpl(self::DIRS_COUNT, self::CHARS_FOR_DIR);
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
            array(new FileId('abcdefghijk'), 'abc/def/abcdefghijk'),
        );
    }
}