<?php


namespace Floppy\Tests\Server\FileHandler;


use Floppy\Common\AttributesBag;
use Floppy\Common\FileSource;
use Floppy\Common\FileType;
use Floppy\Common\Stream\StringInputStream;
use Floppy\Server\FileHandler\FilterImageProcessor;
use Floppy\Server\Imagine\FilterFactory;
use Imagine\Filter\FilterInterface;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;

class FilterImageProcessorTest extends \PHPUnit_Framework_TestCase
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
     */
    public function givenTwoFilters_invokeFilters()
    {
        //given

        $ratio = 2;
        $factory = new FilterImageProcessorTest_FilterFactory(array(
            'filter1' => new FilterImageProcessorTest_ResizeFilter($ratio),
            'filter2' => new FilterImageProcessorTest_ResizeFilter($ratio),
            'filter3' => new FilterImageProcessorTest_ResizeFilter($ratio),
        ));
        $processor = new FilterImageProcessor($this->imagine, $factory);

        $image = $this->imagine->create(new Box(100, 100));
        $options = array('filter1' => array('some options1'), 'filter3' => array('some options3'));

        //when

        $actualSource = $processor->process($this->createFileSource($image), new AttributesBag($options));

        //then

        $actualImage = $this->imagine->load($actualSource->content());
        $expectedWidth = $image->getSize()->getWidth() * $ratio*count($options);
        $expectedSize = new Box($expectedWidth, $expectedWidth);

        $this->assertEquals($expectedSize, $actualImage->getSize());
    }

    /**
     * @test
     * @expectedException \Floppy\Server\FileHandler\Exception\FileProcessException
     */
    public function givenOptionsAreNotArray_throwEx()
    {
        //given

        $factory = new FilterImageProcessorTest_FilterFactory(array(
            'filter1' => new FilterImageProcessorTest_ResizeFilter(1),
        ));
        $process = new FilterImageProcessor($this->imagine, $factory);

        $invalidOptions = array('filter1' => 'some options');
        $image = $this->imagine->create(new Box(100, 100));

        //when

        $process->process($this->createFileSource($image), new AttributesBag($invalidOptions));
    }

    private function createFileSource(ImageInterface $image)
    {
        return new FileSource(new StringInputStream($image->get('png')), new FileType('image/png', 'png'));
    }
}

class FilterImageProcessorTest_FilterFactory implements FilterFactory
{
    public $filters = array();

    public function __construct($filters)
    {
        $this->filters = $filters;
    }


    public function createFilter($name, array $options = array())
    {
        $filter = $this->filters[$name];
        $filter->options = $options;

        return $filter;
    }
}

class FilterImageProcessorTest_ResizeFilter implements FilterInterface
{
    public $options;
    public $applyExecuted = false;

    private $resize;

    function __construct($resize)
    {
        $this->resize = $resize;
    }


    public function apply(ImageInterface $image)
    {
        $this->applyExecuted = true;

        return $image->resize($image->getSize()->scale($this->resize));
    }
}
 