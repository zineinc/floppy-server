<?php


namespace Floppy\Tests\Server\FileHandler;


use Floppy\Common\AttributesBag;
use Floppy\Common\FileSource;
use Floppy\Common\Stream\StringInputStream;
use Floppy\Server\FileHandler\ImageProcess;
use Floppy\Server\FileHandler\OptimizationImageProcess;
use ImageOptimizer\Exception\Exception;
use ImageOptimizer\Optimizer;

class OptimizationImageProcessTest extends \PHPUnit_Framework_TestCase
{
    const OPTIMIZED_SUFFIX = 'optimized';

    /**
     * @test
     */
    public function givenSomeFile_optimizedFileSourceShouldBeReturned()
    {
        //given

        $imageProcess = new OptimizationImageProcess(
            new OptimizationImageProcessTest_Optimizer(self::OPTIMIZED_SUFFIX)
        );

        $fileContent = 'content';
        $fileSource = new FileSource(new StringInputStream($fileContent));

        //when

        $actualFileSource = $imageProcess->process($fileSource, new AttributesBag());

        //then
        $expectedContent = $fileContent.self::OPTIMIZED_SUFFIX;
        $this->assertEquals($expectedContent, $actualFileSource->content());
    }

    /**
     * @test
     */
    public function givenSomeFile_optimizationErrorOccured_returnOriginalFileSource()
    {
        //given

        $optimizer = $this->getMock('ImageOptimizer\\Optimizer');

        $imageProcess = new OptimizationImageProcess(
            $optimizer
        );

        $optimizer->expects($this->once())
            ->method('optimize')
            ->will($this->throwException(new Exception()));

        $fileContent = 'content';
        $fileSource = new FileSource(new StringInputStream($fileContent));

        //when

        $actualFileSource = $imageProcess->process($fileSource, new AttributesBag());

        //then

        $this->assertSame($fileSource, $actualFileSource);
    }
}

class OptimizationImageProcessTest_Optimizer implements Optimizer
{
    private $suffixToAppend;

    public function __construct($suffixToAppend)
    {
        $this->suffixToAppend = $suffixToAppend;
    }


    public function optimize($filepath)
    {
        file_put_contents($filepath, $this->suffixToAppend, FILE_APPEND);
    }
}