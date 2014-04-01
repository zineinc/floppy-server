<?php


namespace Floppy\Server\RequestHandler;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsResponseFilter implements ResponseFilter
{
    private $hosts;

    public function __construct(array $hosts)
    {
        $this->hosts = $hosts;
    }


    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function filterResponse(Request $request, Response $response)
    {
        $origin = $request->headers->get('Origin');

        if($this->isSupportedHost($origin)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS');
        }

        return $response;
    }


    private function isSupportedHost($origin)
    {
        foreach($this->hosts as $host) {
            if($this->hostMatches($origin, $host)) {
                return true;
            }
        }
        return false;
    }

    private function hostMatches($host, $pattern)
    {
        $pattern = preg_quote($pattern, '/');
        $pattern = '/^'. /* protocol pattern */ ($pattern[0] !== '*' && substr($pattern, 0, 4) !== 'http' ? 'http(s)?:\/\/' : '' )
            .str_replace(array('\*\.', '\*'), array('.*'), $pattern).'$/';

        return preg_match($pattern, $host) ? true : false;
    }
}