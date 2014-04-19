<?php


namespace Floppy\Server\RequestHandler\Event;


final class Events
{
    const PRE_DOWNLOAD_FILE_PROCESSING = 'preDownloadFileProcessing';
    const POST_DOWNLOAD_FILE_PROCESSING = 'postDownloadFileProcessing';

    private function __construct()
    {
        throw new \BadMethodCallException();
    }
}