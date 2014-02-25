<?php


namespace ZineInc\Storage\Tests\Server\FileHandler;


use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use ZineInc\Storage\Common\AttributesBag;
use ZineInc\Storage\Server\FileHandler\MaxSizeImageProcess;
use ZineInc\Storage\Server\FileSource;
use ZineInc\Storage\Server\FileType;
use ZineInc\Storage\Server\Stream\StringInputStream;

class MaxSizeImageProcessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Imagine
     */
    private $imagine;

    protected function setUp()
    {
        $this->imagine = new Imagine();
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function resizeWhenImageExceedsMaxSize($maxWidth, $maxHeight, $imageWidth, $imageHeight, $expectedImageWidth, $expectedImageHeight)
    {
        //given

        $process = new MaxSizeImageProcess($maxWidth, $maxHeight);
        $fileSource = $this->createImageFileSource($imageWidth, $imageHeight);
        $attrs = new AttributesBag(array());

        //when

        $actualFileSource = $process->process($this->imagine, $fileSource, $attrs);

        //then

        $actualImage = $this->imagine->load($actualFileSource->content());
        $this->assertEquals(new Box($expectedImageWidth, $expectedImageHeight), $actualImage->getSize());
    }

    private function createImageFileSource($width, $height)
    {
        return new FileSource(new StringInputStream($this->imagine->create(new Box($width, $height))->get('jpg')), new FileType('image/jpeg', 'jpeg'));
    }

    public function dataProvider()
    {
        return array(
            array(100, 50, 50, 30, 50, 30),
            array(100, 50, 80, 60, floor(80*5/6), 50),
            array(100, 50, 110, 40, 100, floor(40*100/110)),
            array(100, 50, 200, 60, 100, floor(60*100/200)),
            array(100, 50, 120, 100, floor(120*50/100), 50),
            array(100, 50, 100, 50, 100, 50),
        );
    }
}
 