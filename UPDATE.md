# Update form 0.1.0 to 0.1.1

If you used directly `Floppy\Server\FileHandler\ImageProcess` family classes you should follow migration instructions,
otherwise that BC-breaks do not apply to you.

## "ImageProcess" family classes have been renamed

* `Floppy\Server\FileHandler\ImageProcess` to `Floppy\Server\FileHandler\FileProcessor`
* `Floppy\Server\FileHandler\MaxSizeImageProcess` to `Floppy\Server\FileHandler\MaxSizeImageProcessor`
* `Floppy\Server\FileHandler\FilterImageProcess` to `Floppy\Server\FileHandler\FilterImageProcessor`
* `Floppy\Server\FileHandler\ResizeImageProcess` to `Floppy\Server\FileHandler\ResizeImageProcessor`

## "ImageProcess::process" method signature has been changed

* `ImageProcess::process(ImagineInterface $imagine, FileSource $fileSource, AttributesBag $attrs)` to
  `FileProcessor::process(FileSource $fileSource, AttributesBag $attrs)`
  
## "MaxSizeImageProcess", "FilterImageProcess" and "ResizeImageProcess" constructors signature have been changed

* prepend `ImagineInterface` as first argument

## "RequestHandlerFactory" options renamed

* `fileHandlers.image.beforeStoreImageProcess` to `fileHandlers.image.beforeStoreImageProcessor`
* `fileHandlers.image.beforeSendImageProcess` to `fileHandlers.image.beforeSendImageProcessor`