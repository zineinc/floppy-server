<?php

namespace Floppy\Server\FileHandler;

use Floppy\Server\FileHandler\Exception\FileProcessException;
use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Palette;
use Imagine\Image\Point;
use Floppy\Common\AttributesBag;
use Floppy\Common\FileSource;
use Floppy\Common\Stream\StringInputStream;

/**
 * Legacy class that provides ability to create thumbnails
 *
 * @deprecated
 */
class ResizeImageProcess implements ImageProcess
{
	private $quality;
    private $palette;

	public function __construct($quality = 95)
	{
		$this->quality = (int) $quality;
        $this->palette = new Palette\RGB();
	}


	public function process(ImagineInterface $imagine, FileSource $fileSource, AttributesBag $attrs)
    {
        if(count($attrs->all()) === 0) {
            return $fileSource;
        }

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

				//when crop color is null, then image shouldn't be enlarged when requested size exceeds original
				if($requestedColor === null) {
					if($requestedSize->getWidth() > $newSize->getWidth()) {
						$requestedSize = new Box($newSize->getWidth(), $requestedSize->getHeight());
					}

					if($requestedSize->getHeight() > $newSize->getHeight()) {
						$requestedSize = new Box($requestedSize->getWidth(), $newSize->getHeight());
					}
				}

                $image->resize($newSize);

                if ($requestedSize != $newSize) {
                    $destImage = $imagine->create($requestedSize, $requestedColor === null || $requestedColor > 'ffffff' ? null : $this->palette->color($requestedColor));
                    $x = ($requestedSize->getWidth() - $newSize->getWidth()) / 2;
                    $y = ($requestedSize->getHeight() - $newSize->getHeight()) / 2;
                    $destImage->paste($image, new Point($x, $y));

                    $image = $destImage;
                }
            }

            return new FileSource(
				new StringInputStream(
					$image->get($fileSource->fileType()->extension(), array('quality' => $this->quality))
				),
				$fileSource->fileType(),
                $fileSource->info()->all()
			);
        } catch (\Imagine\Exception\Exception $e) {
            throw new FileProcessException('Image processing error', 0, $e);
        }
    }
}