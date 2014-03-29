<?php


namespace Floppy\Tests\Server\RequestHandler\Security;


use Floppy\Common\FileSource;
use Symfony\Component\HttpFoundation\Request;
use Floppy\Server\RequestHandler\Security\PolicyRule;
use Floppy\Tests\Common\Stub\ChecksumChecker;
use Floppy\Tests\Server\RequestHandler\Security\ZineInc;
use Floppy\Common\Stream\StringInputStream;

class PolicyRuleTest extends \PHPUnit_Framework_TestCase
{
    const VALID_SIGNATURE = 'valid-signature';
    const INVALID_SIGNATURE = 'invalid-signature';

    private $rule;

    protected function setUp()
    {
        $this->rule = new \Floppy\Server\RequestHandler\Security\PolicyRule(new ChecksumChecker(self::VALID_SIGNATURE));
    }

    /**
     * @test
     * @expectedException Floppy\Server\RequestHandler\AccessDeniedException
     */
    public function givenInvalidSignature_throwEx()
    {
        //given

        $request = $this->createRequest(self::INVALID_SIGNATURE, $this->createValidPolicy());

        //when

        $this->invokeRule($request);
    }

    /**
     * @test
     * @expectedException Floppy\Server\RequestHandler\AccessDeniedException
     */
    public function givenPolicyExpired_throwEx()
    {
        //given

        $request = $this->createRequest(self::VALID_SIGNATURE, $this->createExpiredPolicy());

        //when

        $this->invokeRule($request);
    }

    /**
     * @test
     */
    public function givenValidSignatureAndPolicy_ok()
    {
        //given

        $request = $this->createRequest(self::VALID_SIGNATURE, $this->createValidPolicy());

        //when

        $this->invokeRule($request);
    }

    private function createRequest($signature, $policy)
    {
        $request = new Request();
        $request->request->set('signature', $signature);
        $request->request->set('policy', $policy);

        return $request;
    }

    private function createValidPolicy()
    {
        return base64_encode(json_encode(array(
            'expiration' => time() + 60*5,
        )));
    }

    private function createExpiredPolicy()
    {
        return base64_encode(json_encode(array(
            'expiration' => time() - 5,
        )));
    }

    /**
     * @param $request
     */
    protected function invokeRule($request)
    {
        $this->rule->checkFileSource($request, new FileSource(new StringInputStream('')));
    }
}
 