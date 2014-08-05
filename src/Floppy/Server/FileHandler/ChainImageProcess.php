<?php


namespace Floppy\Server\FileHandler;


use Floppy\Common\AttributesBag;
use Floppy\Common\FileSource;
use Floppy\Server\FileHandler\Exception\FileProcessException;
use Imagine\Image\ImagineInterface;

class ChainImageProcess implements ImageProcess
{
    private $imageProcesses = array();

    public function __construct(array $imageProcesses = array())
    {
        foreach($imageProcesses as $imageProcess) {
            $this->addProcess($imageProcess);
        }
    }

    private function addProcess(ImageProcess $imageProcess)
    {
        $this->imageProcesses[] = $imageProcess;
    }

    public function process(FileSource $fileSource, AttributesBag $attrs)
    {
        foreach($this->imageProcesses as $imageProcess) {
            $fileSource = $imageProcess->process($fileSource, $attrs);
        }

        return $fileSource;
    }
}