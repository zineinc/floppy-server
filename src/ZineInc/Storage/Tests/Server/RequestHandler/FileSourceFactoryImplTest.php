<?php

namespace ZineInc\Storage\Tests\Server\RequestHandler;

use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use ZineInc\Storage\Server\FileType;
use ZineInc\Storage\Server\RequestHandler\FileSourceFactoryImpl;

class FileSourceFactoryImplTest extends PHPUnit_Framework_TestCase
{
    const FILE_KEY = 'file';

    private $factory;

    protected function setUp()
    {
        $this->factory = new FileSourceFactoryImpl(self::FILE_KEY);
    }

    /**
     * @test
     * @dataProvider fileProvider
     */
    public function fileExists_createFileSource($filepath, $mimeType, $ext)
    {
        //given

        $request = $this->createRequestWithFile($filepath);

        //when

        $fileSource = $this->factory->createFileSource($request);

        //then

        $this->assertNotNull($fileSource);
        $this->assertEquals(new FileType($mimeType, $ext), $fileSource->fileType());

        $expectedContent = file_get_contents($filepath);

        $this->assertEquals($expectedContent, $fileSource->content());

        $fileSource->discard();
    }

    /**
     * @test
     * @expectedException ZineInc\Storage\Server\RequestHandler\FileSourceNotFoundException
     */
    public function fileDoesntExist_throwFileSourceNotFoundEx()
    {
        //given

        $request = new Request();

        //when

        $this->factory->createFileSource($request);
    }

    private function createRequestWithFile()
    {
        $request = new Request();
        $file = new UploadedFile(__DIR__.'/../../Resources/text.txt', 'text.txt');
        $request->files->set(self::FILE_KEY, $file);

        return $request;
    }

    public function fileProvider()
    {
        return array(
            array(__DIR__.'/../../Resources/text.txt', 'text/plain', 'txt'),
        );
    }
}