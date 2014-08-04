<?php


namespace Floppy\Tests\Server\FileHandler;

use Floppy\Common\AttributesBag;
use Floppy\Common\FileSource;
use Floppy\Common\Stream\StringInputStream;
use Floppy\Server\FileHandler\ChainImageProcess;
use Floppy\Server\FileHandler\Exception\FileProcessException;
use Floppy\Server\FileHandler\ImageProcess;
use Imagine\Image\ImagineInterface;

class ChainImageProcessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function outputOfPreviousProcessShouldBeInputOfNextOne()
    {
        //given

        $chain = new ChainImageProcess(array(
            new ChainImageProcessTest_ImageProcess('first'),
            new ChainImageProcessTest_ImageProcess(' and second process'),
        ));

        $fileSource = new FileSource(new StringInputStream('executed '));

        //when

        $actualFileSource = $chain->process(
            $this->getMock('Imagine\Image\ImagineInterface'),
            $fileSource,
            new AttributesBag()
        );

        //then

        $this->assertEquals('executed first and second process', $actualFileSource->content());
    }
}

class ChainImageProcessTest_ImageProcess implements ImageProcess
{
    private $append;

    public function __construct($append)
    {
        $this->append = $append;
    }

    public function process(ImagineInterface $imagine, FileSource $fileSource, AttributesBag $attrs)
    {
        return new FileSource(new StringInputStream($fileSource->content().$this->append), $fileSource->fileType());
    }
}