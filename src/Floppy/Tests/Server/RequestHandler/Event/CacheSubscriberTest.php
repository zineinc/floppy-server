<?php


namespace Floppy\Tests\Server\RequestHandler\Event;


use Floppy\Common\FileId;
use Floppy\Server\RequestHandler\Event\CacheSubscriber;
use Floppy\Server\RequestHandler\Event\DownloadEvent;
use Floppy\Server\RequestHandler\Event\ETagGenerator;
use Symfony\Component\HttpFoundation\Request;

class CacheSubscriberTest extends \PHPUnit_Framework_TestCase
{
    const SUPPORTED_FILE_HANDLER_NAME = 'abc';
    const EXPIRES = '+2 years';
    const MAX_AGE = 123;
    const VALID_ETAG = '"etag123"';

    /**
     * @test
     */
    public function preProcessing_useEtagStrategy_etagMissingInRequest_donotCreateResponse()
    {
        //given

        $subscriber = $this->createSubscriber(CacheSubscriber::STRATEGY_ETAG);
        $request = new Request();

        //when

        $event = $this->createEvent($request);
        $subscriber->onPreDownloadFileProcessing($event);

        //then

        $this->assertNull($event->getResponse());
    }

    /**
     * @test
     */
    public function preProcessing_useExpiresStrategy_lastModifiedMissing_donotCreateResponse()
    {
        //given

        $subscriber = $this->createSubscriber(CacheSubscriber::STRATEGY_EXPIRES);
        $request = new Request();

        //when

        $event = $this->createEvent($request);
        $subscriber->onPreDownloadFileProcessing($event);

        //then

        $this->assertNull($event->getResponse());
    }

    /**
     * @test
     */
    public function preProcessing_useEtagStrategy_givenValidEtagInRequest_createNotModifiedResponse()
    {
        //given

        $subscriber = $this->createSubscriber(CacheSubscriber::STRATEGY_ETAG);
        $request = new Request();
        $request->headers->set('if-none-match', self::VALID_ETAG);

        //when

        $event = $this->createEvent($request);
        $subscriber->onPreDownloadFileProcessing($event);

        //then

        $this->assertNotNull($event->getResponse());
        $this->assertEquals(304, $event->getResponse()->getStatusCode());
        $this->assertEquals(self::VALID_ETAG, $event->getResponse()->getEtag());
    }

    private function createSubscriber($strategy)
    {
        return new CacheSubscriber(array(
            self::SUPPORTED_FILE_HANDLER_NAME
        ), $strategy, self::EXPIRES, self::MAX_AGE, new CacheSubscriberTest_ETagGenerator(self::VALID_ETAG));
    }

    private function createEvent(Request $request)
    {
        return new DownloadEvent(new FileId('id'), $request, self::SUPPORTED_FILE_HANDLER_NAME);
    }
}

class CacheSubscriberTest_ETagGenerator implements ETagGenerator {
    private $etag;

    public function __construct($etag)
    {
        $this->etag = $etag;
    }

    public function generateETag(FileId $fileId)
    {
        return $this->etag;
    }
}
 