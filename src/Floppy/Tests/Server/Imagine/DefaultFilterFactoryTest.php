<?php


namespace Floppy\Tests\Server\Imagine;

use Floppy\Server\Imagine\DefaultFilterFactory;
use Imagine\Filter\FilterInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;

class DefaultFilterFactoryTest extends \PHPUnit_Framework_TestCase
{
    const VALID_NAME = 'name';

    private $imagine;
    private $rootPath;

    /**
     * @var DefaultFilterFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->imagine = $this->getMock('Imagine\Image\ImagineInterface');
        $this->rootPath = 'some/path';

        $this->factory = new DefaultFilterFactory($this->imagine, $this->rootPath);
    }

    /**
     * @test
     */
    public function register0ArgsFilter_filterCanBeCreated()
    {
        //given

        $classname = 'Floppy\Tests\Server\Imagine\DefaultFilterFactoryTest_0ArgsFilter';

        $this->factory->registerFilter(self::VALID_NAME, $classname);

        //when

        $filter = $this->factory->createFilter(self::VALID_NAME, array());

        //then

        $this->assertInstanceOf($classname, $filter);
    }

    /**
     * @test
     */
    public function register1ArgFilter_filterCanBeCreated()
    {
        //given

        $classname = 'Floppy\Tests\Server\Imagine\DefaultFilterFactoryTest_1ArgFilter';

        $this->factory->registerFilter(self::VALID_NAME, $classname);
        $options = array('some');

        //when

        $filter = $this->factory->createFilter(self::VALID_NAME, $options);

        //then

        $this->assertInstanceOf($classname, $filter);
        $this->assertEquals($options, $filter->options);
    }

    /**
     * @test
     */
    public function register2ArgsFilter_filterCanBeCreated()
    {
        //given

        $classname = 'Floppy\Tests\Server\Imagine\DefaultFilterFactoryTest_2ArgsFilter';

        $this->factory->registerFilter(self::VALID_NAME, $classname);
        $options = array('valid options');

        //when

        $filter = $this->factory->createFilter(self::VALID_NAME, $options);

        //then

        $this->assertInstanceOf($classname, $filter);
        $this->assertEquals($options, $filter->options);
        $this->assertEquals($this->imagine, $filter->imagine);
    }

    /**
     * @test
     */
    public function register3ArgsFilter_filterCanBeCreated()
    {
        //given

        $classname = 'Floppy\Tests\Server\Imagine\DefaultFilterFactoryTest_3ArgsFilter';

        $this->factory->registerFilter(self::VALID_NAME, $classname);
        $options = array('valid options');

        //when

        $filter = $this->factory->createFilter(self::VALID_NAME, $options);

        //then

        $this->assertInstanceOf($classname, $filter);
        $this->assertEquals($options, $filter->options);
        $this->assertEquals($this->imagine, $filter->imagine);
        $this->assertEquals($this->rootPath, $filter->rootPath);
    }

    /**
     * @test
     * @expectedException \Floppy\Server\Imagine\Exception\InvalidFilterException
     */
    public function registerClassThatDoesntExist_throwEx()
    {
        //given

        $classname = 'Class\Doesnt\Exists';

        $this->factory->registerFilter(self::VALID_NAME, $classname);
    }

    /**
     * @test
     * @expectedException \Floppy\Server\Imagine\Exception\FilterNotFoundException
     */
    public function createUnregisteredFilter_throwEx()
    {
        $this->factory->createFilter('unexisted filter', array());
    }

    /**
     * @test
     * @expectedException \Floppy\Server\Imagine\Exception\InvalidFilterOptionsException
     */
    public function filterThrowsInvalidArgEx_wrapEx()
    {
        //given

        $classname = 'Floppy\Tests\Server\Imagine\DefaultFilterFactoryTest_0ArgsInvalidOptionsFilter';
        $this->factory->registerFilter(self::VALID_NAME, $classname);

        //when

        $this->factory->createFilter(self::VALID_NAME, array());
    }
}

class DefaultFilterFactoryTest_0ArgsFilter implements FilterInterface
{
    public function apply(ImageInterface $image)
    {
        return $image;
    }
}

class DefaultFilterFactoryTest_0ArgsInvalidOptionsFilter implements FilterInterface
{
    public function __construct(array $options)
    {
        throw new \InvalidArgumentException();
    }

    public function apply(ImageInterface $image)
    {
        return $image;
    }
}

class DefaultFilterFactoryTest_1ArgFilter implements FilterInterface
{
    public $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function apply(ImageInterface $image)
    {
        return $image;
    }
}

class DefaultFilterFactoryTest_2ArgsFilter implements FilterInterface
{
    public $imagine;
    public $options;

    public function __construct(ImagineInterface $imagine, array $options)
    {
        $this->options = $options;
        $this->imagine = $imagine;
    }

    public function apply(ImageInterface $image)
    {
        return $image;
    }
}


class DefaultFilterFactoryTest_3ArgsFilter implements FilterInterface
{
    public $imagine;
    public $options;
    public $rootPath;

    public function __construct(ImagineInterface $imagine, $rootPath, array $options)
    {
        $this->options = $options;
        $this->imagine = $imagine;
        $this->rootPath = $rootPath;
    }

    public function apply(ImageInterface $image)
    {
        return $image;
    }
}