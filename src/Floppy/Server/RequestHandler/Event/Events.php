<?php


namespace Floppy\Server\RequestHandler\Event;


final class Events
{
    const PRE_DOWNLOAD_FILE_PROCESSING = 'preDownloadFileProcessing';
    const POST_DOWNLOAD_FILE_PROCESSING = 'postDownloadFileProcessing';
    const HTTP_REQUEST = 'httpRequest';
    const HTTP_RESPONSE = 'httpResponse';

    private function __construct()
    {
        throw new \BadMethodCallException();
    }
}