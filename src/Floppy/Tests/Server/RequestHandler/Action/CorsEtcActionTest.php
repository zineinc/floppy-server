<?php

namespace Floppy\Tests\Server\RequestHandler\Action;

use Floppy\Server\RequestHandler\Action\CorsEtcAction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Tests\RequestMatcherTest;

class CorsEtcActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider corsDataProvider
     */
    public function givenOptionsRequestWithOrigin($allowedHosts, $origin, $expectedSuccess)
    {
        //given

        $action = new CorsEtcAction($allowedHosts);

        //when

        $response = $action->execute($this->givenCorsRequest($origin));

        //then

        if($expectedSuccess)
        {
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($origin, $response->headers->get('Access-Control-Allow-Origin'));
        }
        else
        {
            $this->assertEquals(404, $response->getStatusCode());
        }
    }

    private function givenCorsRequest($origin)
    {
        $request = new Request();
        $request->headers->set('Origin', $origin);
        $request->setMethod('OPTIONS');

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
 