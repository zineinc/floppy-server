<?php


namespace Floppy\Server\Imagine;

use Floppy\Server\Imagine\Exception\FilterNotFoundException;
use Floppy\Server\Imagine\Exception\InvalidFilterException;
use Floppy\Server\Imagine\Exception\InvalidFilterOptionsException;
use Imagine\Image\ImagineInterface;

class DefaultFilterFactory implements FilterFactory
{
    private $filters = array();

    private $constructors = array();
    private $classes = array();

    private $imagine;
    private $rootPath;

    public function __construct(ImagineInterface $imagine, $rootPath)
    {
        $this->imagine = $imagine;
        $this->rootPath = $rootPath;
    }

    /**
     * Filters with 4 constructors are supported:
     *
     * * 0-args constructor
     * * 1-arg constructor: array $options
     * * 2-args constructor: ImagineInterface, array $options
     * * 3-args constructor: ImagineInterface, $rootPath, array $options
     *
     * @param $name
     * @param $classname
     *
     * @throws InvalidFilterException when filter class doesn\'t exist
     */
    public function registerFilter($name, $classname)
    {
        if(!class_exists($classname, true)) {
            throw new InvalidFilterException(sprintf('Class "%s" for filter %s doesn\'t exist', $classname, $name));
        }

        $this->filters[$name] = $classname;
    }

    public function createFilter($name, array $options = array())
    {
        if(!isset($this->filters[$name])) {
            throw new FilterNotFoundException($name);
        }

        $classname = $this->filters[$name];

        try {
            $args = $this->getConstructorArgs($classname, $options);
            $class = $this->getClass($classname);

            return $class->newInstanceArgs($args);
        } catch(\InvalidArgumentException $e) {
            throw new InvalidFilterOptionsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param $classname
     * @return \ReflectionMethod
     */
    private function getConstructor($classname)
    {
        if(!isset($this->constructors[$classname]))
        {
            $class = $this->getClass($classname);
            $this->constructors[$classname] = $class->getConstructor();
        }

        return $this->constructors[$classname];
    }

    /**
     * @param $classname
     * @return \ReflectionClass
     */
    private function getClass($classname)
    {
        if(!isset($this->classes[$classname]))
        {
            $this->classes[$classname] = new \ReflectionClass($classname);
        }

        return $this->classes[$classname];
    }

    /**
     * @param $classname
     * @param array $options
     * @return array
     *
     * @throws InvalidFilterException When filter class has unsupported constructor
     */
    protected function getConstructorArgs($classname, array $options)
    {
        $constructor = $this->getConstructor($classname);
        $constructorArgsCount = $constructor ? count($constructor->getParameters()) : 0;

        switch($constructorArgsCount) {
            case 0:
                return array();
            case 1:
                return array($options);
            case 2:
                return array($this->imagine, $options);
            case 3:
                return array($this->imagine, $this->rootPath, $options);
            default:
                throw new InvalidFilterException(sprintf('Filter with class "%s" should to has 0 to 3 constructor args, %d given', $classname, $constructorArgsCount));
        }
    }
}