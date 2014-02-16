<?php

namespace ZineInc\Storage\Server\FileHandler;

use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use InvalidArgumentException;
use ZineInc\Storage\Server\FileId;
use ZineInc\Storage\Server\FileSource;
use ZineInc\Storage\Server\FileType;
use ZineInc\Storage\Server\Stream\StringStream;

class ImageFileHandler implements FileHandler
{
    const TYPE = 'i';

    private static $defaultSupportedMimeTypes = array(
        'image/png',
        'image/jpeg',
        'image/pjpeg',
        'image/jpg',
        'image/gif',
    );
    private $imagine;

    private $options = array(
        'supportedMimeTypes' => null,
        'maxWidth' => 1920,
        'maxHeight' => 1200,
    );

    public function __construct(ImagineInterface $imagine, array $options = array())
    {
        $this->options['supportedMimeTypes'] = self::$defaultSupportedMimeTypes;
        
        $this->setOptions($options);

        $this->imagine = $imagine;
    }

    public function setOptions(array $options)
    {
        foreach($options as $name => $value) {
            if(!array_key_exists($name, $this->options)) {
                throw new InvalidArgumentException(sprintf('Option "%s" is not supported by class "%s"', $name, get_class($this)));
            }

            if($value !== null) {
                $this->options[$name] = $value;
            }
        }
    }

    public function beforeSendProcess(FileSource $file, FileId $fileId)
    {
        
    }

    public function beforeStoreProcess(FileSource $file)
    {
        try
        {
            $image = $this->imagine->load($this->fileContent($file));

            $size = $image->getSize();

            if($size->getWidth() <= $this->options['maxWidth'] && $size->getHeight() <= $this->options['maxHeight']) {
                return $file;
            }

            $ratio = $size->getWidth()/$size->getHeight();

            $newSize = $ratio > 1 ? new Box($this->options['maxWidth'], $this->options['maxHeight']/$ratio)
                    : new Box($this->options['maxWidth']*$ratio, $this->options['maxHeight']);

            $image->resize($newSize);

            $content = $image->get($file->fileType()->prefferedExtension());
            $file->stream()->close();

            return new FileSource(new StringStream($content), $file->fileType());
        }
        catch(\Imagine\Exception\Exception $e)
        {
            throw new FileProcessException('Image before store processing error', $e);
        }
    }

    private function fileContent(FileSource $file)
    {
        $file->stream()->resetInput();
        return $file->stream()->read();
    }

    public function getStoreAttributes(FileSource $file)
    {
        try
        {
            $file->stream()->resetInput();
            $content = $file->stream()->read();

            $image = $this->imagine->load($content);
            $size = $image->getSize();

            return array(
                'width' => $size->getWidth(),
                'height' => $size->getHeight(),
                'mime-type' => $file->fileType()->mimeType(),
                'extension' => $file->fileType()->prefferedExtension(),
                'size' => strlen($content),
            );
        }
        catch(\Imagine\Exception\Exception $e)
        {
            throw new FileProcessException('Image load error', $e);
        }
    }

    public function supports(FileType $fileType)
    {
        return in_array($fileType->mimeType(), $this->options['supportedMimeTypes']);
    }

    public function type()
    {
        return static::TYPE;
    }
}