<?php


namespace Floppy\Server\RequestHandler;

use Floppy\Server\RequestHandler\Action\CorsEtcAction;
use Floppy\Server\RequestHandler\Action\DownloadAction;
use Floppy\Server\RequestHandler\Action\UploadAction;
use Floppy\Server\RequestHandler\Security\NullRule;
use Symfony\Component\HttpFoundation\Request;
use Floppy\Common\ChecksumCheckerImpl;
use Floppy\Common\FileHandler\FilePathMatcher;
use Floppy\Common\FileHandler\ImagePathMatcher;
use Floppy\Server\FileHandler\DispositionResponseFilter;
use Floppy\Server\FileHandler\FallbackFileHandler;
use Floppy\Server\FileHandler\ImageFileHandler;
use Floppy\Server\FileHandler\MaxSizeImageProcess;
use Floppy\Server\FileHandler\ResizeImageProcess;
use Floppy\Common\Storage\FilepathChoosingStrategyImpl;
use Floppy\Server\RequestHandler\Security\CallableFirewall;
use Floppy\Server\Storage\FilesystemStorage;
use Floppy\Server\Storage\IdFactoryImpl;

class RequestHandlerFactory
{

    /**
     * @return RequestHandlerFactory
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Creates RequestHandler instance
     *
     * Required options:
     *
     * * storage.dir - root directory where files should be stored
     * * secretKey - salt for checksums - it should be the same as in storage clients
     *
     * Important optional options:
     *
     * * fileHandlers.file.mimeTypes - array of supported mime-types for non-image files
     * * fileHandlers.file.extensions - array of supported extensions for non-image files
     * * fileHandlers - array of Floppy\Server\FileHandler\FileHandler instances, by default ImageFileHandler and FallbackFileHandler is active
     *
     * For more info follow to implementation of this method.
     *
     * @param array $options
     *
     * @return RequestHandler
     */
    public function createRequestHandler(array $options)
    {
        $container = new \Pimple();

        $this->storageDefinitions($container);
        $this->checksumCheckerDefinition($container);
        $this->requestHandlerDefinition($container);
        $this->fileHandlersDefinition($container);

        foreach ($options as $name => $value) {
            $container[$name] = $value;
        }

        return $container['requestHandler'];
    }

    /**
     * @param $container
     */
    private function storageDefinitions(\Pimple $container)
    {
        $container['storage'] = function ($container) {
            return new FilesystemStorage(
                $container['storage.dir'],
                $container['storage.filepathChoosingStrategy'],
                $container['storage.idFactory'],
                $container['storage.dirChmod'],
                $container['storage.fileChmod']
            );
        };
        $container['storage.filepathChoosingStrategy'] = function ($container) {
            return new FilepathChoosingStrategyImpl();
        };
        $container['storage.idFactory'] = function ($container) {
            return new IdFactoryImpl();
        };
        $container['storage.fileChmod'] = 0644;
        $container['storage.dirChmod'] = 0755;
    }

    /**
     * @param $container
     */
    private function fileHandlersDefinition($container)
    {
        $container['fileHandlers'] = function ($container) {
            return array(
                'image' => $container['fileHandlers.image'],
                'file' => $container['fileHandlers.file'],
            );
        };
        $container['fileHandlers.image'] = function ($container) {
            return new ImageFileHandler(
                $container['imagine'],
                $container['fileHandlers.image.pathMatcher'],
                $container['fileHandlers.image.beforeStoreImageProcess'],
                $container['fileHandlers.image.beforeSendImageProcess'],
                $container['fileHandlers.image.responseFilters'],
                array(
                    'supportedMimeTypes' => $container['fileHandlers.image.mimeTypes'],
                    'supportedExtensions' => $container['fileHandlers.image.extensions']
                )
            );
        };
        $container['fileHandlers.image.responseFilters'] = array();
        $container['fileHandlers.image.mimeTypes'] = ImageFileHandler::getDefaultSupportedMimeTypes();
        $container['fileHandlers.image.extensions'] = ImageFileHandler::getDefaultSupportedExtensions();
        $container['imagine'] = function () {
            return new \Imagine\Gd\Imagine();
        };
        $container['fileHandlers.image.pathMatcher'] = function ($container) {
            return new ImagePathMatcher($container['checksumChecker'], $container['fileHandlers.image.extensions']);
        };
        $container['fileHandlers.image.beforeSendImageProcess'] = function ($container) {
            return new ResizeImageProcess();
        };
        $container['fileHandlers.image.beforeStoreImageProcess'] = function($container) {
            return new MaxSizeImageProcess($container['fileHandlers.image.maxWidth'], $container['fileHandlers.image.maxHeight']);
        };
        $container['fileHandlers.image.maxWidth'] = 1920;
        $container['fileHandlers.image.maxHeight'] = 1200;
        $container['fileHandlers.file'] = function ($container) {
            return new FallbackFileHandler(
                $container['fileHandlers.file.pathMatcher'],
                $container['fileHandlers.file.mimeTypes'],
                $container['fileHandlers.file.extensions'],
                $container['fileHandlers.file.responseFilters']
            );
        };
        $container['fileHandlers.file.pathMatcher'] = function ($container) {
            return new FilePathMatcher($container['checksumChecker'], $container['fileHandlers.file.extensions']);
        };
        $container['fileHandlers.file.mimeTypes'] = function ($container) {
            return array();
        };
        $container['fileHandlers.file.extensions'] = function ($container) {
            return array();
        };
        $container['fileHandlers.file.responseFilters'] = array(
            new DispositionResponseFilter(),
        );
    }

    /**
     * @param $container
     */
    private function checksumCheckerDefinition($container)
    {
        $container['checksumChecker'] = function ($container) {
            return new ChecksumCheckerImpl($container['secretKey'], $container['checksumChecker.length']);
        };
        $container['checksumChecker.length'] = -1;
        return $container;
    }

    /**
     * @param $container
     */
    private function requestHandlerDefinition($container)
    {
        $container['requestHandler'] = function ($container) {
            return new RequestHandler(
                $container['actionResolver'],
                $container['requestHandler.firewall'],
                $container['requestHandler.corsFilter']
            );
        };
        $container['actionResolver'] = function($container){
            $resolver = new ActionResolverImpl();

            $resolver->register($container['action.cors'], function(Request $request){
                return $request->isMethod('options') || in_array($request->getPathInfo(), array('/crossdomain.xml', '/clientaccesspolicy.xml'));
            })->register($container['action.upload'], function(Request $request){
                return rtrim($request->getPathInfo(), '/') === '/upload';
            })->register($container['action.download'], function(Request $request){
                return true;
            });

            return $resolver;
        };

        $container['action.upload'] = function($container){
            return new UploadAction($container['storage'], $container['action.upload.fileSourceFactory'], $container['fileHandlers'], $container['checksumChecker'], $container['action.upload.securityRule']);
        };
        $container['action.cors'] = function($container){
            return new CorsEtcAction($container['action.cors.allowedOriginHosts']);
        };
        $container['action.download'] = function($container){
            return new DownloadAction($container['storage'], $container['action.download.responseFactory'], $container['fileHandlers'], $container['action.download.securityRule']);
        };

        $container['action.download.securityRule'] = $container['action.upload.securityRule'] = function($container){
            return new NullRule();
        };

        $container['action.cors.allowedOriginHosts'] = array();
        $container['action.upload.fileSourceFactory'] = function ($container) {
            return new FileSourceFactoryImpl();
        };
        $container['action.download.responseFactory'] = function($container) {
            return new DownloadResponseFactoryImpl();
        };
        $container['requestHandler.firewall'] = function($container) {
            return new CallableFirewall(array(
                DownloadAction::name() => $container['requestHandler.firewall.download'],
                UploadAction::name() => $container['requestHandler.firewall.upload'],
            ));
        };
        $container['requestHandler.firewall.download'] = function($container) {
            return function(Request $request) {
                //allow download
            };
        };
        $container['requestHandler.firewall.upload'] = function($container) {
            return function(Request $request) {
                //allow upload
            };
        };
        $container['requestHandler.corsFilter'] = function($container){
            return new CorsResponseFilter($container['action.cors.allowedOriginHosts']);
        };

        return $container;
    }
} 