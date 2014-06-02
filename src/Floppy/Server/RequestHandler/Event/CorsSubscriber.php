<?php


namespace Floppy\Server\RequestHandler\Event;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsSubscriber implements EventSubscriberInterface
{
    private $hosts;

    public function __construct(array $hosts)
    {
        $this->hosts = $hosts;
    }


    public static function getSubscribedEvents()
    {
        return array(
            Events::HTTP_REQUEST => 'onRequest',
            Events::HTTP_RESPONSE => 'onResponse',
        );
    }

    public function onRequest(HttpEvent $event)
    {
        $request = $event->getRequest();

        if($request->headers->has('Origin')) {
            $response = $this->buildCorsResponse($request);

            if($response !== null) {
                $event->setResponse($response);
            }
        } else if($request->getPathInfo() === '/crossdomain.xml') {
            $event->setResponse($this->buildCrossdomainResponse());
        } else if($request->getPathInfo() === '/clientaccesspolicy.xml') {
            $event->setResponse($this->buildClientAccessPolicyResponse());
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
        if($pattern === '*') {
            return true;
        }

        $pattern = preg_quote($pattern, '/');
        $pattern = '/^'. /* protocol pattern */ ($pattern[0] !== '*' && substr($pattern, 0, 4) !== 'http' ? 'http(s)?:\/\/' : '' )
            .str_replace(array('\*\.', '\*'), array('.*'), $pattern).'$/';

        return preg_match($pattern, $host) ? true : false;
    }

    /**
     * @param $request
     * @return null|Response
     */
    private function buildCorsResponse(Request $request)
    {
        $origin = $request->headers->get('Origin');

        if ($request->isMethod('Options')) {
            return new Response('', 200, array(
                'Access-Control-Allow-Origin' => $this->getAllowedOrigin($origin),
                'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS',
            ));
        } else if ($origin !== null) {
            if (!$this->isSupportedHost($origin)) {
                return new Response('', 403, array(
                    'Access-Control-Allow-Origin' => 'null',
                ));
            }
        }

        return null;
    }


    private function buildCrossdomainResponse()
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

        return new Response($content, 200, array(
            'Content-Type' => 'text/xml',
        ));
    }

    private function buildClientAccessPolicyResponse()
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

        return new Response($content, 200, array(
            'Content-Type' => 'text/xml',
        ));
    }

    public function onResponse(HttpEvent $event)
    {
        $request = $event->getRequest();

        $origin = $request->headers->get('Origin');

        if($origin !== null) {
            $event->getResponse()->headers->add(array(
                'Access-Control-Allow-Origin' => $this->getAllowedOrigin($origin),
                'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS',
            ));
        }
    }

    /**
     * @param $origin
     * @return string
     */
    private function getAllowedOrigin($origin)
    {
        $allowedOrigin = $this->isSupportedHost($origin) ? $origin : 'null';
        return $allowedOrigin;
    }
}