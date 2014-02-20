<?php

namespace ZineInc\Storage\Tests\Server\FileHandler;

use Imagine\Gd\Imagine;
use PHPUnit_Framework_TestCase;
use ZineInc\Storage\Server\FileHandler\ImageFileHandler;
use ZineInc\Storage\Server\FileSource;
use ZineInc\Storage\Server\FileType;
use ZineInc\Storage\Server\Stream\StringInputStream;

class ImageFileHandlerTest extends PHPUnit_Framework_TestCase
{
    const MAX_WIDTH = 10;
    const MAX_HEIGHT = 10;

    /**
     * @var \ZineInc\Storage\Common\FileHandler\\ZineInc\Storage\Server\FileHandler\ImageFileHandler
     */
    private $handler;
    
    /**
     * @var Imagine
     */
    private $imagine;

    protected function setUp()
    {
        $this->imagine = new Imagine();

        $this->handler = new ImageFileHandler($this->imagine, array(), $this->getMock('ZineInc\Storage\Common\FileHandler\VariantMatcher'));
    }

    /**
     * @test
     * @dataProvider fileTypeProvider
     */
    public function shouldSupportImageFileTypes($mimeType, $ext, $expectedSupports)
    {
        //given

        $fileType = new FileType($mimeType, $ext);

        //when

        $actualSupports = $this->handler->supports($fileType);

        //then

        $this->assertEquals($expectedSupports, $actualSupports);
    }

    public function fileTypeProvider()
    {
        return array(
            array('image/png', 'png', true),
            array('text/plain', 'txt', false),
        );
    }

    /**
     * @test
     */
    public function shouldBuildCorrectAttributesForImage()
    {
        //given

        $fileSource = $this->createImageFileSource(__DIR__.'/../../Resources/100x80.png');

        //when

        $attrs = $this->handler->getStoreAttributes($fileSource);

        //then

        $this->assertEquals(100, $attrs['width']);
        $this->assertEquals(80, $attrs['height']);
        $this->assertTrue($attrs['size'] > 0);
    }

    private function createImageFileSource($path)
    {
        return new FileSource(new StringInputStream(file_get_contents($path)), new FileType('image/png', 'png'));
    }

    /**
     * @test
     */
    public function processBeforeStore_imageIsLarge_resizeIt()
    {
        //given

        $maxSize = 10;
        $this->handler->setOptions(array(
            'maxHeight' => $maxSize,
            'maxWidth' => $maxSize
        ));
        $fileSource = $this->createImageFileSource(__DIR__.'/../../Resources/100x80.png');

        //when

        $actualSource = $this->handler->beforeStoreProcess($fileSource);

        //then

        $image = $this->imagine->load($actualSource->content());

        $this->assertEquals($maxSize, $image->getSize()->getWidth());
    }

    /**
     * @test
     */
    public function processBeforeStore_imageIsSmall_dontTouchIt()
    {
        //given

        $maxSize = 100;
        $this->handler->setOptions(array(
            'maxHeight' => $maxSize,
            'maxWidth' => $maxSize
        ));

        $fileSource = $this->createImageFileSource(__DIR__.'/../../Resources/100x80.png');

        //when

        $actualSource = $this->handler->beforeStoreProcess($fileSource);

        //then

        $this->assertTrue($actualSource === $fileSource);
    }
}