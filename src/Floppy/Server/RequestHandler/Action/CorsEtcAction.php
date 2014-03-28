<?php


namespace Floppy\Server\RequestHandler\Action;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsEtcAction implements Action
{
    private $hosts;

    public function __construct(array $hosts)
    {
        $this->hosts = $hosts;
    }

    public function execute(Request $request)
    {
        $response = new Response();

        if(!$this->hosts) {
            $response->setStatusCode(404);
            return $response;
        }

        if($this->isCorsRequest($request)) {
            $this->processCorsRequest($request, $response);
        } elseif ($request->getPathInfo() === '/crossdomain.xml') {
            $this->buildCrossdomain($response);
        } elseif($request->getPathInfo() === '/clientaccesspolicy.xml') {
            $this->buildClientAccessPolicy($response);
        } else {
            $response->setStatusCode(404);
        }

        return $response;
    }

    private function isCorsRequest(Request $request)
    {
        return $request->isMethod('options') && $request->headers->has('Origin');
    }

    private function processCorsRequest(Request $request, Response $response)
    {
        $origin = $request->headers->get('Origin');

        if($this->isSupportedHost($origin)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS');
        } else {
            $response->setStatusCode(404);
        }
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

    private function buildCrossdomain(Response $response)
    {
        $allowAccessElements = implode(\PHP_EOL, array_map(function($host){
            return '<allow-access-from domain="'.$host.'"/>';
        }, $this->hosts));

        $content = <<<XML
<?xml version="1.0"?>
<!DOCTYPE cross-domain-policy SYSTEM "http://www.adobe.com/xml/dtds/cross-domain-policy.dtd">
<cross-domain-policy>
    <site-control permitted-cross-domain-policies="master-only"/>
    $allowAccessElements
</cross-domain-policy>
XML;

        $response->setContent($content);
        $response->headers->set('Content-Type', 'text/xml');
    }

    private function buildClientAccessPolicy(Response $response)
    {
        $allowedDomains = implode(PHP_EOL, array_map(function($host){
            return '<domain uri="'.$host.'"/>';
        },$this->hosts));

        $content = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<access-policy>
    <cross-domain-access>
        <policy>
            <allow-from http-request-headers="*">
                $allowedDomains
            </allow-from>
            <grant-to>
                <resource path="/" include-subpaths="true"/>
            </grant-to>
        </policy>
    </cross-domain-access>
</access-policy>
XML;

        $response->setContent($content);
        $response->headers->set('Content-Type', 'text/xml');
    }
}