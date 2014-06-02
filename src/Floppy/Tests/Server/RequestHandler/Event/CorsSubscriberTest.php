<?php


namespace Floppy\Tests\Server\RequestHandler\Event;


use Floppy\Server\RequestHandler\Event\CorsSubscriber;
use Floppy\Server\RequestHandler\Event\HttpEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider corsOptionsRequestSuccessProvider
     */
    public function corsOptionsRequestSuccess($allowedHosts, $origin)
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

    public function corsOptionsRequestSuccessProvider()
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
    public function corsOptionsRequestFailure($allowedHosts, $origin)
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
     * @dataProvider corsOptionsRequestSuccessProvider
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
    private function createHttpOptionsEvent($origin)
    {
        $event = $this->createHttpEvent($origin);
        $event->getRequest()->setMethod('Options');
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
 