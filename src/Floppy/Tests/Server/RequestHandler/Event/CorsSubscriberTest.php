<?php


namespace Floppy\Tests\Server\RequestHandler\Event;


use Floppy\Server\RequestHandler\Event\CorsSubscriber;
use Floppy\Server\RequestHandler\Event\HttpEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsSubscriberTest extends \PHPUnit_Framework_TestCase
{
    const SUPPORTED_METHOD = 'GET';
    const UNSUPPORTED_METHOD = 'POST';

    /**
     * @test
     * @dataProvider preflightRequestSuccessProvider
     */
    public function preflightRequestSuccess($allowedHosts, $origin)
    {
        //given

        $subscriber = new CorsSubscriber($allowedHosts);
        $event = $this->createHttpOptionsEvent($origin);

        //when

        $subscriber->onRequest($event);

        //then

        $response = $event->getResponse();

        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($origin, $response->headers->get('Access-Control-Allow-Origin'));
    }

    public function preflightRequestSuccessProvider()
    {
        return array(
            array(
                array('*.example.com'), 'http://example.com',
            ),
            array(
                array('*.example.com'), 'http://some.extra.example.com',
            ),
            array(
                array('*.example.com'), 'https://some.extra.example.com',
            ),
            array(
                array('http://example.com'), 'http://example.com',
            ),
            array(
                array('*'), 'http://example.com',
            ),
            array(
                array('*.some.com', '*.example.com'), 'http://example.com',
            ),
        );
    }

    /**
     * @test
     * @dataProvider corsRequestFailureProvider
     */
    public function preflightRequestFailure($allowedHosts, $origin)
    {
        //given

        $subscriber = new CorsSubscriber($allowedHosts);
        $event = $this->createHttpOptionsEvent($origin);

        //when

        $subscriber->onRequest($event);

        //then

        $response = $event->getResponse();

        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('null', $response->headers->get('Access-Control-Allow-Origin'));
    }

    public function corsRequestFailureProvider()
    {
        return array(
            array(
                array('*.example.com'), 'some.com',
            ),
            array(
                array('*.extra.example.com'), 'example.com',
            ),
            array(
                array('https://extra.example.com'), 'http://extra.example.com',
            ),
        );
    }

    /**
     * @test
     */
    public function preflightRequestFailure_notAllowedMethod()
    {
        //given

        $host = 'http://example.com';
        $subscriber = new CorsSubscriber(array($host), array('allowedMethods' => array(self::SUPPORTED_METHOD)));
        $event = $this->createHttpOptionsEvent($host, self::UNSUPPORTED_METHOD);

        //when

        $subscriber->onRequest($event);
        
        //then


        $response = $event->getResponse();

        $this->assertNotNull($response);
        $this->assertEquals(405, $response->getStatusCode());
    }

    /**
     * @test
     * @dataProvider corsRequestFailureProvider
     */
    public function corsNotOptionsRequestFailure($allowedHosts, $origin)
    {
        //given

        $subscriber = new CorsSubscriber($allowedHosts);
        $event = $this->createHttpEvent($origin);

        //when

        $subscriber->onRequest($event);

        //then

        $response = $event->getResponse();

        $this->assertNotNull($response);
        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * @test
     * @dataProvider preflightRequestSuccessProvider
     */
    public function corsResponseOriginMatches($allowedHosts, $origin)
    {
        //given

        $subscriber = new CorsSubscriber($allowedHosts);
        $event = $this->createHttpEvent($origin);
        $event->setResponse(new Response());

        //when

        $subscriber->onResponse($event);

        //then

        $response = $event->getResponse();
        $this->assertEquals($origin, $response->headers->get('Access-Control-Allow-Origin'));
    }

    /**
     * @test
     * @dataProvider corsRequestFailureProvider
     */
    public function corsResponseOriginFailure($allowedHosts, $origin)
    {
        //given

        $subscriber = new CorsSubscriber($allowedHosts);
        $event = $this->createHttpEvent($origin);
        $event->setResponse(new Response());

        //when

        $subscriber->onResponse($event);

        //then

        $response = $event->getResponse();
        $this->assertEquals('null', $response->headers->get('Access-Control-Allow-Origin'));
    }

    /**
     * @param $origin
     * @return HttpEvent
     */
    private function createHttpOptionsEvent($origin, $requestMethod = self::SUPPORTED_METHOD)
    {
        $event = $this->createHttpEvent($origin);
        $event->getRequest()->setMethod('Options');
        $event->getRequest()->headers->set('Access-Control-Request-Method', $requestMethod);
        return $event;
    }

    private function createHttpEvent($origin)
    {
        $request = new Request();
        $request->headers->set('Origin', $origin);
        $event = new HttpEvent($request);
        return $event;
    }
}
 