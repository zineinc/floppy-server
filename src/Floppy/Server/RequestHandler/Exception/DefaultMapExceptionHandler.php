<?php


namespace Floppy\Server\RequestHandler\Exception;


class DefaultMapExceptionHandler extends MapExceptionHandler
{
    public function __construct()
    {
        parent::__construct(array(
            'Floppy\Server\FileHandler\Exception\MatchingFileHandlerNotFoundException' => array(404, 'file-not-found'),
            'Floppy\Server\FileHandler\Exception\FileHandlerNotFoundException' => array(400, 'unsupported-file-type'),
            'Floppy\Server\FileHandler\Exception\FileProcessException' => array(500, 'server-error'),

            'Floppy\Server\RequestHandler\Exception\AccessDeniedException' => array(403, 'access-denied'),
            'Floppy\Server\RequestHandler\Exception\BadRequestException' => array(400, 'bad-request'),
            'Floppy\Server\RequestHandler\Exception\FileHandlerNotFoundException' => array(400, 'unsupported-file-type'),
            'Floppy\Server\RequestHandler\Exception\FileSourceNotFoundException' => array(400, 'file-not-found'),

            'Floppy\Server\Storage\Exception\FileSourceNotFoundException' => array(404, 'file-not-found'),
            'Floppy\Server\Storage\Exception\StoreException' => array(500, 'server-error'),

            'Floppy\Server\RequestHandler\Action\Exception\ActionNotFoundException' => array(404, 'file-not-found'),

            'Floppy\Common\FileHandler\Exception\PathMatchingException' => array(404, 'file-not-found'),
            'Floppy\Common\Stream\Exception\IOException' => array(500, 'server-error'),

            'Floppy\Common\Exception\StorageError' => array(500, 'server-error'),
            'Floppy\Common\Exception\StorageException' => array(400, 'bad-request'),
            'Exception' => array(500, 'server-error'),
        ));
    }
} 