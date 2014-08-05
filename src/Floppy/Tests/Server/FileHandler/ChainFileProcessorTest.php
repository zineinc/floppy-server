<?php


namespace Floppy\Tests\Server\FileHandler;

use Floppy\Common\AttributesBag;
use Floppy\Common\FileSource;
use Floppy\Common\Stream\StringInputStream;
use Floppy\Server\FileHandler\ChainFileProcessor;
use Floppy\Server\FileHandler\Exception\FileProcessException;
use Floppy\Server\FileHandler\FileProcessor;
use Imagine\Image\ImagineInterface;

class ChainFileProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function outputOfPreviousProcessShouldBeInputOfNextOne()
    {
        //given

        $chain = new ChainFileProcessor(array(
            new ChainFileProcessorTest_ImageProcess('first'),
            new ChainFileProcessorTest_ImageProcess(' and second process'),
        ));

        $fileSource = new FileSource(new StringInputStream('executed '));

        //when

        $actualFileSource = $chain->process($fileSource, new AttributesBag());

        //then

        $this->assertEquals('executed first and second process', $actualFileSource->content());
    }
}

class ChainFileProcessorTest_ImageProcess implements FileProcessor
{
    private $append;

    public function __construct($append)
    {
        $this->append = $append;
    }

    public function process(FileSource $fileSource, AttributesBag $attrs)
    {
        return new FileSource(new StringInputStream($fileSource->content().$this->append), $fileSource->fileType());
    }
}