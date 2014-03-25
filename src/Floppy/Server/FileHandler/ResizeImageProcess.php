<?php

namespace Floppy\Server\FileHandler;

use Imagine\Image\Box;
use Imagine\Image\Color;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;
use Floppy\Common\AttributesBag;
use Floppy\Common\FileSource;
use Floppy\Common\Stream\StringInputStream;

class ResizeImageProcess implements ImageProcess
{
    public function process(ImagineInterface $imagine, FileSource $fileSource, AttributesBag $attrs)
    {
        try {
            $image = $imagine->load($fileSource->content());

            $size = $image->getSize();
            $ratio = $size->getWidth() / $size->getHeight();

            $requestedWidth = $attrs->get('width');
            $requestedHeight = $attrs->get('height');
            $requestedRatio = $requestedWidth / $requestedHeight;
            $requestedSize = new Box($requestedWidth, $requestedHeight);

            if ($attrs->get('crop')) {
                $newSize = $ratio > $requestedRatio ? new Box($requestedHeight * $ratio, $requestedHeight)
                    : new Box($requestedWidth, $requestedWidth / $ratio);

                $image->resize($newSize);

                if ($ratio > $requestedRatio) {
                    $x = 0;
                    $y = ($requestedSize->getHeight() - $newSize->getHeight()) / 2;
                } else {
                    $x = ($requestedSize->getWidth() - $newSize->getWidth()) / 2;
                    $y = 0;
                }

                $image->crop(new Point($x, $y), $requestedSize);
            } else {

                //requested size is greater than original size, skip processing, return original image
                if ($requestedSize->getWidth() >= $size->getWidth() && $requestedSize->getHeight() >= $size->getHeight()) {
                    return $fileSource;
                }

                $requestedColor = $attrs->get('cropBackgroundColor');

                $newSize = $ratio > $requestedRatio ? new Box($requestedWidth, $requestedWidth / $ratio)
                    : new Box($requestedHeight * $ratio, $requestedHeight);

                $image->resize($newSize);

                if ($requestedSize != $newSize) {
                    $destImage = $imagine->create($requestedSize, $requestedColor > 'ffffff' ? null : new Color($requestedColor));
                    $x = ($requestedSize->getWidth() - $newSize->getWidth()) / 2;
                    $y = ($requestedSize->getHeight() - $newSize->getHeight()) / 2;
                    $destImage->paste($image, new Point($x, $y));

                    $image = $destImage;
                }
            }

            return new FileSource(new StringInputStream($image->get($fileSource->fileType()->prefferedExtension())), $fileSource->fileType());
        } catch (\Imagine\Exception\Exception $e) {
            throw new FileProcessException('Image processing error', $e);
        }
    }
}