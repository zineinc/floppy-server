<?php

namespace Floppy\Tests\Server\FileHandler;


use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;
use Floppy\Common\AttributesBag;
use Floppy\Server\FileHandler\ResizeImageProcessor;
use Floppy\Common\FileSource;
use Floppy\Common\FileType;
use Floppy\Common\Stream\StringInputStream;

class ResizeImageProcessorTest extends \PHPUnit_Framework_TestCase
{
    const CROP_COLOR = 'eeeeee';
    const DELTA = 2;

    /**
     * @var ImagineInterface
     */
    private $imagine;
    private $processor;

    protected function setUp()
    {
        $this->imagine = new Imagine();
        $this->processor = new ResizeImageProcessor($this->imagine);
    }

    /**
     * @test
     * @dataProvider sizeProvider
     */
    public function cropMissing_resizeToGivenSize($width, $height)
    {
        //given

        $fileSource = $this->createImageFileSource(__DIR__ . '/../../Resources/100x80-black.png');
        $attrs = new AttributesBag(array('width' => $width, 'height' => $height, 'crop' => false, 'cropBackgroundColor' => self::CROP_COLOR));

        //when

        $actualFileSource = $this->processor->process($fileSource, $attrs);

        //then

        $image = $this->imagine->load($actualFileSource->content());

        $this->assertEquals(new Box($width, $height), $image->getSize());

        $this->assertColorAt($image, '#' . self::CROP_COLOR, new Point(0, 0), 'color of first point should be cropBackgroundColor');
        $this->assertColorAt($image, '#000000', new Point($width / 2, $height / 2), 'color of middle point should be black');

        $originalImageRatio = 100 / 80;
        $requestedRatio = $width / $height;
        $expectedPastedOriginalImageHeight = $originalImageRatio > $requestedRatio ? $width / $originalImageRatio : $height;

        $expectedFirstY = ceil(($height - $expectedPastedOriginalImageHeight) / 2);

        $this->assertColorAt($image, '#000000', new Point($width/2, $expectedFirstY), 'this should be point where original image was pasted');
        $this->assertColorAt($image, '#000000', new Point($width/2, $expectedFirstY + $expectedPastedOriginalImageHeight - self::DELTA), 'this should be last point of original image');

        if($expectedFirstY > self::DELTA) {
            $this->assertColorAt($image, '#' . self::CROP_COLOR, new Point($width/2, $expectedFirstY - self::DELTA), 'this should be point outside original image');
        }

        if($expectedFirstY + $expectedPastedOriginalImageHeight + self::DELTA <= $height) {
            $this->assertColorAt($image, '#' . self::CROP_COLOR, new Point($width/2, $expectedFirstY + $expectedPastedOriginalImageHeight + self::DELTA), 'this should be point outside original image');
        }
    }

    public function sizeProvider()
    {
        return array(
            array(20, 40),
            array(20, 22),
            array(20, 10),
            array(20, 18),
        );
    }

    /**
     * @test
     */
    public function croppedMissing_requestSizeGreaterThanOriginal_doesntProcessImageAndReturnOriginal()
    {
        //given

        $fileSource = $this->createImageFileSource(__DIR__ . '/../../Resources/100x80-black.png');
        $attrs = new AttributesBag(array('width' => 500, 'height' => 500, 'crop' => false, 'cropBackgroundColor' => self::CROP_COLOR));

        //when

        $actualFileSource = $this->processor->process($fileSource, $attrs);

        //then

        $this->assertEquals($fileSource, $actualFileSource);
    }

	/**
	 * @test
	 */
	public function croppedMissingAndCropBackgroundMissing_oneRequestedDimensionGreaterThanOriginal_resizeToSmallerDimensionAndFitRatio()
	{
		//given

        $info = array('name' => 'value');
		$fileSource = $this->createImageFileSource(__DIR__ . '/../../Resources/100x80-black.png', $info);
		$attrs = new AttributesBag(array('width' => 500, 'height' => 40, 'crop' => false, 'cropBackgroundColor' => null));

		//when

		$actualFileSource = $this->processor->process($fileSource, $attrs);

		//then

        $this->assertEquals($info, $actualFileSource->info()->all());

		$image = $this->imagine->load($actualFileSource->content());

		$this->assertEquals(new Box(50, 40), $image->getSize());
	}

    /**
     * @test
     */
    public function emptyAttributes_doesntProcessImageAndReturnOriginal()
    {
        //given

        $fileSource = $this->createImageFileSource(__DIR__ . '/../../Resources/100x80-black.png');
        $attrs = new AttributesBag();

        //when

        $actualFileSource = $this->processor->process($fileSource, $attrs);

        //then

        $this->assertEquals($fileSource, $actualFileSource);
    }

    /**
     * @test
     * @dataProvider sizeProvider
     */
    public function croppedAttrExists_cropToGivenSize($width, $height)
    {
        //given

        $fileSource = $this->createImageFileSource(__DIR__ . '/../../Resources/100x80-black.png');
        $attrs = new AttributesBag(array('width' => $width, 'height' => $height, 'crop' => true, 'cropBackgroundColor' => self::CROP_COLOR));

        //when

        $actualFileSource = $this->processor->process($fileSource, $attrs);

        //then

        $image = $this->imagine->load($actualFileSource->content());

        $this->assertEquals(new Box($width, $height), $image->getSize());

        for($i=0; $i<$height; $i++)
        {
            $this->assertEquals('#000000', (string) $image->getColorAt(new Point(0, $i)));
            $this->assertEquals('#000000', (string) $image->getColorAt(new Point($width/2, $i)));
            $this->assertEquals('#000000', (string) $image->getColorAt(new Point($width - 1, $i)));
        }
    }

    private function assertColorAt($image, $expectedColor, $point, $message = null)
    {
        $actualColor = $image->getColorAt($point);
        $this->assertEquals($expectedColor, (string)$actualColor, $message);
    }

    private function createImageFileSource($path, array $info = array())
    {
        return new FileSource(new StringInputStream(file_get_contents($path)), new FileType('image/png', 'png'), $info);
    }
}
 