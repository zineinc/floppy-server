<?php


namespace ZineInc\Storage\Tests\Server;


use Symfony\Component\HttpFoundation\File\File;
use ZineInc\Storage\Server\FileSource;
use ZineInc\Storage\Server\FileType;

class FileSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testCreateFromFile()
    {
        //given

        $filepath = __DIR__.'/../Resources/text.txt';
        $file = new File($filepath);

        //when

        $fileSource = FileSource::fromFile($file);

        //then
        $expectedContent = file_get_contents($filepath);
        $this->assertEquals($expectedContent, $fileSource->content());
        $this->assertEquals(new FileType('text/plain', 'txt'), $fileSource->fileType());
    }
}
 