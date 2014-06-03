<?php


namespace Floppy\Server\RequestHandler\Event;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsSubscriber implements EventSubscriberInterface
{
    private $hosts;
    private $options = array(
        'allowedMethods' => array('GET', 'POST'),
        'maxAge' => 0,
        'allowedHeaders' => array(),
        'allowCredentials' => false,
        'exposedHeaders' => array(),
    );

    public function __construct(array $hosts, array $options = array())
    {
        $this->hosts = $hosts;

        if($extraOptions = array_diff_key($options, $this->options)) {
            throw new \InvalidArgumentException(sprintf('Invalid options provided: %s, supported options: %s',
                implode(', ', array_keys($extraOptions)), implode(', ', array_keys($this->options))));
        }

        $this->options = array_merge($this->options, $options);

        $this->options['allowedMethods'] = array_map(function($value){
            return strtoupper($value);
        }, $this->options['allowedMethods']);
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
            $headers = $this->getCorsHeaders($origin, true);
            $requestMethod = strtoupper($request->headers->get('Access-Control-Request-Method'));

            return new Response('', in_array($requestMethod, $this->options['allowedMethods']) ? 200 : 405, $headers);
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
            $event->getResponse()->headers->add($this->getCorsHeaders($origin, false));
        }
    }

    private function getAllowedOrigin($origin)
    {
        $allowedOrigin = $this->isSupportedHost($origin) ? $origin : 'null';
        return $allowedOrigin;
    }

    private function getCorsHeaders($origin, $preflightRequest = false)
    {
        $headers = array(
            'Access-Control-Allow-Origin' => $this->getAllowedOrigin($origin),
        );

        if($preflightRequest && $this->options['allowedMethods']) {
            $headers['Access-Control-Allow-Methods'] = implode(', ', $this->options['allowedMethods']);
        }

        if($preflightRequest && $this->options['allowedHeaders']) {
            $headers['Access-Control-Allow-Headers'] = $this->options['allowedHeaders'];
        }

        if($this->options['allowCredentials']) {
            $headers['Access-Control-Allow-Credentials'] = 'true';
        }

        if(!$preflightRequest && $this->options['exposedHeaders']) {
            $headers['Access-Control-Expose-Headers'] = implode(', ', $this->options['exposedHeaders']);
        }

        if($preflightRequest && $this->options['maxAge']) {
            $headers['Access-Control-Max-Age'] = $this->options['maxAge'];
        }

        return $headers;
    }
}