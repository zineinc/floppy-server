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
    const HEIGHT_GREATER_THAN_WIDTH = 30;
    const CROP_COLOR = 'eeeeee';
    const DELTA = 2;

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
        $attrs = new AttributesBag(array('width' => self::WIDTH, 'height' => self::HEIGHT_GREATER_THAN_WIDTH, 'cropBackgroundColor' => self::CROP_COLOR));
        
        //when

        $actualFileSource = $this->process->process($this->imagine, $fileSource, $attrs);
        
        //then

        $image = $this->imagine->load($actualFileSource->content());

        $this->assertEquals(new Box(self::WIDTH, self::HEIGHT_GREATER_THAN_WIDTH), $image->getSize());

        $this->assertColorAt($image, '#'.self::CROP_COLOR, new Point(0, 0), 'color of first point should be cropBackgroundColor');
        $this->assertColorAt($image, '#000000', new Point(self::WIDTH/2, self::HEIGHT_GREATER_THAN_WIDTH/2), 'color of middle point should be black');

        $originalImageRatio = 100/80;
        $expectedPastedOriginalImageHeight = self::WIDTH/$originalImageRatio;

        $expectedFirstY = ceil((self::HEIGHT_GREATER_THAN_WIDTH - $expectedPastedOriginalImageHeight)/2);

        $this->assertColorAt($image, '#000000', new Point(0, $expectedFirstY), 'this should be point where original image was pasted');
        $this->assertColorAt($image, '#000000', new Point(0, $expectedFirstY+$expectedPastedOriginalImageHeight-self::DELTA), 'this should be last point of original image');
        $this->assertColorAt($image, '#'.self::CROP_COLOR, new Point(0, $expectedFirstY-self::DELTA), 'this should be point outside original image');
        $this->assertColorAt($image, '#'.self::CROP_COLOR, new Point(0, $expectedFirstY+$expectedPastedOriginalImageHeight+self::DELTA), 'this should be point outside original image');
    }

    private function assertColorAt($image, $expectedColor, $point, $message = null)
    {
        $actualColor = $image->getColorAt($point);
        $this->assertEquals($expectedColor, (string) $actualColor, $message);
    }

    private function createImageFileSource($path)
    {
        return new FileSource(new StringInputStream(file_get_contents($path)), new FileType('image/png', 'png'));
    }
}
 