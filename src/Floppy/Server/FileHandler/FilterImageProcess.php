<?php


namespace Floppy\Server\FileHandler;


use Floppy\Common\AttributesBag;
use Floppy\Common\FileSource;
use Floppy\Common\Stream\StringInputStream;
use Floppy\Server\FileHandler\Exception\FileProcessException;
use Floppy\Server\Imagine\FilterFactory;
use Imagine\Image\ImagineInterface;

class FilterImageProcess implements ImageProcess
{
    private $imagine;
    private $filterFactory;
    private $defaultQuality;

    public function __construct(ImagineInterface $imagine, FilterFactory $filterFactory, $defaultQuality = 90)
    {
        $this->imagine = $imagine;
        $this->filterFactory = $filterFactory;
        $this->defaultQuality = (int) $defaultQuality;
    }

    public function process(FileSource $fileSource, AttributesBag $attrs)
    {
        if(count($attrs->all()) === 0) {
            return $fileSource;
        }

        try {
            return $this->doProcess($fileSource, $attrs);
        } catch (\Imagine\Exception\Exception $e) {
            throw new FileProcessException('Image processing error', 0, $e);
        }
    }

    private function doProcess(FileSource $fileSource, AttributesBag $attrs)
    {
        $image = $this->imagine->load($fileSource->content());

        $quality = $this->defaultQuality;

        foreach ($attrs->all() as $filterName => $options) {
            if ($filterName === 'quality') {
                $quality = (int) $options;
            } else {
                if(!is_array($options)) {
                    throw new FileProcessException(sprintf('Options for filter %s should be an array, %s given', $filterName, gettype($options)));
                }

                $filter = $this->filterFactory->createFilter($filterName, $options);
                $image = $filter->apply($image);
            }
        }

        return $this->createFileSource($image, $fileSource, $quality);
    }

    private function createFileSource($image, FileSource $originalFileSource, $quality)
    {
        return new FileSource(
            new StringInputStream(
                $image->get($originalFileSource->fileType()->extension(), array('quality' => $quality))
            ),
            $originalFileSource->fileType(),
            $originalFileSource->info()->all()
        );
    }
}