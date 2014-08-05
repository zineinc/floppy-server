* 0.1.1 (2014-08-05)

    * add image files optimizations (thanks to [ImageOptimizer](https://github.com/psliwa/image-optimizer) library), it can be enabled by `fileHandlers.image.enableOptimizations` option of `RequestHandlerFactory`::`createRequestHandler` method
    * api **BC-break**: `ImageProcess` family classes refactoring, rename interface to `FileProcessor`, change interface method signature and more - migration instructions in [UPDATE.md](UPDATE.md) file

* 0.1.0 (2014-07-05)

    * first release