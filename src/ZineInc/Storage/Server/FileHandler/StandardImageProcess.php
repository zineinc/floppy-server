<?php

namespace ZineInc\Storage\Server\FileHandler;

use Imagine\Image\Box;
use Imagine\Image\Color;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;
use ZineInc\Storage\Common\AttributesBag;
use ZineInc\Storage\Server\FileSource;
use ZineInc\Storage\Server\Stream\StringInputStream;

class StandardImageProcess implements ImageProcess
{
    public function process(ImagineInterface $imagine, FileSource $fileSource, AttributesBag $attrs)
    {
        $image = $imagine->load($fileSource->content());

        $size = $image->getSize();
        $ratio = $size->getWidth()/$size->getHeight();

        $requestedWidth = $attrs->get('width');
        $requestedHeight = $attrs->get('height');
        $requestedSize = new Box($requestedWidth, $requestedHeight);

        $requestedColor = $attrs->get('cropBackgroundColor');

        $newSize = $ratio > 1 ? new Box($requestedWidth, $requestedWidth/$ratio)
            : new Box($requestedHeight*$ratio, $requestedHeight);

        $image->resize($newSize);

        if($requestedSize != $newSize)
        {
            $destImage = $imagine->create($requestedSize, $requestedColor > 'ffffff' ? null : new Color($requestedColor));
            $x = ($requestedSize->getWidth() - $newSize->getWidth())/2;
            $y = ($requestedSize->getHeight() - $newSize->getHeight())/2;
            $destImage->paste($image, new Point($x, $y));

            $image = $destImage;
        }

        return new FileSource(new StringInputStream($image->get($fileSource->fileType()->prefferedExtension())), $fileSource->fileType());
    }
}