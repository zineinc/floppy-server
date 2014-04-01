<?php

namespace Floppy\Tests\Server\RequestHandler\Action;

use Floppy\Server\RequestHandler\Action\CorsEtcAction;
use Floppy\Server\RequestHandler\CorsResponseFilter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Tests\RequestMatcherTest;

class CorsResponseFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider corsDataProvider
     */
    public function givenOptionsRequestWithOrigin($allowedHosts, $origin, $expectedSuccess)
    {
        //given

        $action = new CorsResponseFilter($allowedHosts);

        //when


        $response = $action->filterResponse($this->givenCorsRequest($origin), new Response());

        //then

        $this->assertEquals($expectedSuccess ? $origin : null, $response->headers->get('Access-Control-Allow-Origin'));
    }

    private function givenCorsRequest($origin)
    {
        $request = new Request();
        $request->headers->set('Origin', $origin);

        return $request;
    }

    public function corsDataProvider()
    {
        return array(
            array(
                array('*.example.com'), 'http://example.com', true,
            ),
            array(
                array('*.example.com'), 'http://example2.com', false,
            ),
            array(
                array('example.com'), 'http://example.com', true,
            ),
            array(
                array('example.com'), 'http://www.example.com', false,
            ),
            array(
                array('https://example.com'), 'http://example.com', false,
            ),
            array(
                array('http://example.com'), 'http://example.com', true,
            ),
        );
    }
}
 