<?php

namespace ZineInc\Storage\Tests\Server\Stream;

use ZineInc\Storage\Server\Stream\StringStream;

class StringStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testReadBytes()
    {
        $streamContent = 'abcdefghi';
        $stream = new StringStream($streamContent);

        $this->assertEquals('a', $stream->read(1));
        $this->assertEquals('b', $stream->read(1));
        $this->assertEquals('cd', $stream->read(2));
        $this->assertEquals('efghi', $stream->read(8));
        $this->assertNull($stream->read(1));

        $stream->resetInput();

        $this->assertEquals($streamContent, $stream->read());
        $this->assertNull($stream->read(1));

        $stream->resetInput();

        $stream->read(2);
        $this->assertEquals('cdefghi', $stream->read());
    }

    /**
     * @test
     */
    public function testWriteBytes()
    {
        $stream = new StringStream();

        $stream->write('abcd');

        $this->assertEquals('abcd', $stream->read());

        $stream->resetInput();

        $stream->write('efg');

        $this->assertEquals('abcdefg', $stream->read());
    }

    /**
     * @test
     */
    public function testOverwriteBytes()
    {
        $stream = new StringStream('ab');

        $stream->write('c');

        $this->assertEquals('cb', $stream->read());

        $stream->resetInput();

        $stream->write('ccc');

        $this->assertEquals('cccc', $stream->read());
    }
}