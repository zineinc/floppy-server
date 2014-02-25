<?php

namespace ZineInc\Storage\Server\FileHandler;

use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use InvalidArgumentException;
use ZineInc\Storage\Common\AttributesBag;
use ZineInc\Storage\Common\FileHandler\PathMatcher;
use ZineInc\Storage\Common\FileId;
use ZineInc\Storage\Server\FileHandler\AbstractFileHandler;
use ZineInc\Storage\Server\FileHandler\FileProcessException;
use ZineInc\Storage\Server\FileSource;
use ZineInc\Storage\Server\Stream\StringInputStream;

class ImageFileHandler extends AbstractFileHandler
{
    const TYPE = 'i';

    private static $defaultSupportedMimeTypes = array(
        'image/png',
        'image/jpeg',
        'image/pjpeg',
        'image/jpg',
        'image/gif',
    );
    private static $defaultSupportedExtensions = array(
        'png', 'jpeg', 'gif', 'jpg'
    );
    private $imagine;
    private $beforeStoreImageProcess;
    private $beforeSendImageProcess;

    private $options = array(
        'supportedMimeTypes' => null,
        'supportedExtensions' => null,
    );

    public function __construct(ImagineInterface $imagine, PathMatcher $variantMatcher, ImageProcess $beforeStoreImageProcess, ImageProcess $beforeSendImageProcess, array $responseFilters = array(), array $options = array())
    {
        parent::__construct($variantMatcher, $responseFilters);

        $this->options['supportedMimeTypes'] = self::$defaultSupportedMimeTypes;
        $this->options['supportedExtensions'] = self::$defaultSupportedExtensions;

        $this->setOptions($options);

        $this->imagine = $imagine;
        $this->beforeSendImageProcess = $beforeSendImageProcess;
        $this->beforeStoreImageProcess = $beforeStoreImageProcess;
    }

    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            if (!array_key_exists($name, $this->options)) {
                throw new InvalidArgumentException(sprintf('Option "%s" is not supported by class "%s"', $name, get_class($this)));
            }

            if ($value !== null) {
                $this->options[$name] = $value;
            }
        }
    }

    public function beforeSendProcess(FileSource $file, FileId $fileId)
    {
        return $this->beforeSendImageProcess->process($this->imagine, $file, $fileId->attributes());
    }

    public function beforeStoreProcess(FileSource $file)
    {
        return $this->beforeStoreImageProcess->process($this->imagine, $file, new AttributesBag());
    }

    protected function doGetStoreAttributes(FileSource $file, $content)
    {
        try {
            $image = $this->imagine->load($content);
            $size = $image->getSize();

            return array(
                'width' => $size->getWidth(),
                'height' => $size->getHeight(),
            );
        } catch (\Imagine\Exception\Exception $e) {
            throw new FileProcessException('Image load error', $e);
        }
    }

    protected function supportedMimeTypes()
    {
        return $this->options['supportedMimeTypes'];
    }

    protected function supportedExtensions()
    {
        return $this->options['supportedExtensions'];
    }
}