<?php


namespace Floppy\Server\FileHandler;


use Floppy\Common\AttributesBag;
use Floppy\Common\FileSource;
use Floppy\Common\Stream\StringInputStream;
use Floppy\Server\FileHandler\Exception\FileProcessException;
use ImageOptimizer\Exception\Exception;
use ImageOptimizer\Optimizer;
use Imagine\Image\ImagineInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class OptimizationImageProcess implements ImageProcess
{
    private $optimizer;
    private $logger;

    public function __construct(Optimizer $optimizer, LoggerInterface $logger = null)
    {
        $this->optimizer = $optimizer;
        $this->logger = $logger ?: new NullLogger();
    }

    public function process(ImagineInterface $imagine, FileSource $fileSource, AttributesBag $attrs)
    {
        $filename = tempnam(sys_get_temp_dir(), 'floppy');

        if(!@file_put_contents($filename, $fileSource->content())) {
            return $fileSource;
        }

        try {
            $fileSource = $this->optimize($filename, $fileSource);
        } catch (Exception $e) {
            $this->logger->error($e);
        }

        @unlink($filename);

        return $fileSource;
    }

    private function optimize($filename, FileSource $fileSource)
    {
        $this->optimizer->optimize($filename);

        $fileSource->discard();

        $fileSource = new FileSource(new StringInputStream(file_get_contents($filename)), $fileSource->fileType(), $fileSource->info()->all());

        return $fileSource;
    }
}