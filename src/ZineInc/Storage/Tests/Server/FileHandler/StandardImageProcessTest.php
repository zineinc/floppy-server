<?php

namespace ZineInc\Storage\Tests\Server\FileHandler;


use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;
use ZineInc\Storage\Common\AttributesBag;
use ZineInc\Storage\Server\FileHandler\StandardImageProcess;
use ZineInc\Storage\Server\FileSource;
use ZineInc\Storage\Server\FileType;
use ZineInc\Storage\Server\Stream\StringInputStream;

class StandardImageProcessTest extends \PHPUnit_Framework_TestCase
{
    const WIDTH = 20;
    const HEIGHT = 30;
    const CROP_COLOR = 'eeeeee';

    /**
     * @var ImagineInterface
     */
    private $imagine;
    private $process;

    protected function setUp()
    {
        $this->imagine = new Imagine();
        $this->process = new StandardImageProcess();
    }
    
    /**
     * @test
     */
    public function cropMissing_resizeToGivenSize()
    {
        //given

        $fileSource = $this->createImageFileSource(__DIR__.'/../../Resources/100x80-black.png');
        $attrs = new AttributesBag(array('width' => self::WIDTH, 'height' => self::HEIGHT, 'cropBackgroundColor' => self::CROP_COLOR));
        
        //when

        $actualFileSource = $this->process->process($this->imagine, $fileSource, $attrs);
        
        //then

        $image = $this->imagine->load($actualFileSource->content());

        $this->assertEquals(new Box(self::WIDTH, self::HEIGHT), $image->getSize());
        $this->assertColorAt($image, '#'.self::CROP_COLOR, new Point(0, 0), 'color of first point should be cropBackgroundColor');
        $this->assertColorAt($image, '#000000', new Point(self::WIDTH/2, self::HEIGHT/2), 'color of middle point should be black');
    }

    private function assertColorAt($image, $expectedColor, $point, $message)
    {
        $actualColor = $image->getColorAt($point);
        $this->assertEquals($expectedColor, (string) $actualColor, $message);
    }

    private function createImageFileSource($path)
    {
        return new FileSource(new StringInputStream(file_get_contents($path)), new FileType('image/png', 'png'));
    }
}
 