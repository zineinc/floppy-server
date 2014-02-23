<?php


namespace ZineInc\Storage\Server\RequestHandler;

use ZineInc\Storage\Common\ChecksumCheckerImpl;
use ZineInc\Storage\Common\FileHandler\FilePathMatcher;
use ZineInc\Storage\Common\FileHandler\ImagePathMatcher;
use ZineInc\Storage\Server\FileHandler\FallbackFileHandler;
use ZineInc\Storage\Server\FileHandler\ImageFileHandler;
use ZineInc\Storage\Server\FileHandler\StandardImageProcess;
use ZineInc\Storage\Server\Storage\FilepathChoosingStrategyImpl;
use ZineInc\Storage\Server\Storage\FilesystemStorage;
use ZineInc\Storage\Server\Storage\IdFactoryImpl;

class RequestHandlerFactory {

    /**
     * @return RequestHandlerFactory
     */
    public static function create() {
        return new self();
    }

    public function createRequestHandler(array $options)
    {
        $container = new \Pimple();

        $container['storage'] = function($container){
            return new FilesystemStorage($container['storage.dir'], $container['storage.filepathChoosingStrategy'], $container['storage.idFactory']);
        };
        $container['storage.filepathChoosingStrategy'] = function($container){
            return new FilepathChoosingStrategyImpl();
        };
        $container['storage.idFactory'] = function($container){
            return new IdFactoryImpl();
        };
        $container['checksumChecker'] = function($container){
            return new ChecksumCheckerImpl($container['secretKey'], $container['checksumChecker.length']);
        };
        $container['checksumChecker.length'] = 5;
        $container['requestHandler'] = function($container){
            return new RequestHandler($container['storage'], $container['requestHandler.fileSourceFactory'], $container['fileHandlers']);
        };
        $container['requestHandler.fileSourceFactory'] = function($container) {
            return new FileSourceFactoryImpl();
        };
        $container['fileHandlers'] = function($container){
            return array(
                $container['fileHandlers.image'],
                $container['fileHandlers.file'],
            );
        };
        $container['fileHandlers.image'] = function($container){
            return new ImageFileHandler($container['imagine'], $container['fileHandlers.image.pathMatcher'], $container['fileHandlers.image.imageProcess'], /*TODO*/array());
        };
        $container['imagine'] = function(){
            return new \Imagine\Gd\Imagine();
        };
        $container['fileHandlers.image.pathMatcher'] = function($container){
            return new ImagePathMatcher($container['checksumChecker']);
        };
        $container['fileHandlers.image.imageProcess'] = function($container){
            return new StandardImageProcess();
        };
        $container['fileHandlers.file'] = function($container){
            return new FallbackFileHandler($container['fileHandlers.file.pathMatcher'], $container['fileHandlers.file.mimeTypes']);
        };
        $container['fileHandlers.file.pathMatcher'] = function($container){
            return new FilePathMatcher($container['checksumChecker']);
        };
        $container['fileHandlers.file.mimeTypes'] = function($container){
            return array('text/plain');
        };

        foreach($options as $name => $value)
        {
            $container[$name] = $value;
        }

        return $container['requestHandler'];
    }
} 