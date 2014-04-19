<?php


namespace Floppy\Server\RequestHandler\Event;


use Floppy\Common\FileId;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

class CacheSubscriber implements ETagGenerator, EventSubscriberInterface
{
    const STRATEGY_EXPIRES = 'expires';
    const STRATEGY_ETAG = 'etag';

    private $strategy = self::STRATEGY_EXPIRES;
    private $maxAge = 0;
    private $expires = '+1 year';
    private $cachedFileHandlerNames;
    private $etagGenerator;

    public function __construct(array $cachedFileHandlerNames, $strategy, $expires, $maxAge, ETagGenerator $etagGenerator = null)
    {
        $this->cachedFileHandlerNames = $cachedFileHandlerNames;
        $this->expires = $expires;
        $this->maxAge = $maxAge;
        $this->strategy = $strategy;
        $this->etagGenerator = $etagGenerator ?: $this;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::PRE_DOWNLOAD_FILE_PROCESSING => 'onPreDownloadFileProcessing',
            Events::POST_DOWNLOAD_FILE_PROCESSING => 'onPostDownloadFileProcessing',
        );
    }

    public function onPreDownloadFileProcessing(DownloadEvent $event)
    {
        if(!in_array($event->getFileHandlerName(), $this->cachedFileHandlerNames)) {
            return;
        }

        $request = $event->getRequest();
        $response = new Response();

        $this->setResponseCache($response, $event->getFileId());

        if($response->isNotModified($request)) {
            $event->setResponse($response);
        }
    }

    public function generateETag(FileId $fileId)
    {
        return md5($fileId->id().'|'.serialize($fileId->attributes()));
    }

    public function onPostDownloadFileProcessing(DownloadEvent $event)
    {
        if(!in_array($event->getFileHandlerName(), $this->cachedFileHandlerNames)) {
            return;
        }

        $response = $event->getResponse();
        $this->setResponseCache($response, $event->getFileId());
    }

    /**
     * @param $response
     * @param $currentEtag
     */
    private function setResponseCache(Response $response, FileId $fileId)
    {
        if($this->strategy === self::STRATEGY_ETAG) {
            $currentEtag = $this->etagGenerator->generateEtag($fileId);
            $cache = array(
                'etag' => $currentEtag,
                'public' => true,
            );
            if($this->maxAge) {
                $cache['max_age'] = $this->maxAge;
            }
            $response->setCache($cache);
        } else {
            $expires = new \DateTime();
            $expires->add(\DateInterval::createFromDateString($this->expires));
            $response->setCache(array(
                'public' => true
            ));
            $response->setExpires($expires);
        }
    }
}