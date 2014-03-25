<?php


namespace Floppy\Tests\Server\RequestHandler\Security;


use Symfony\Component\HttpFoundation\Request;
use Floppy\Server\RequestHandler\Security\PolicyGuardRule;
use Floppy\Tests\Common\Stub\ChecksumChecker;
use Floppy\Tests\Server\RequestHandler\Security\ZineInc;

class PolicyGuardRuleTest extends \PHPUnit_Framework_TestCase
{
    const VALID_SIGNATURE = 'valid-signature';
    const INVALID_SIGNATURE = 'invalid-signature';

    private $rule;

    protected function setUp()
    {
        $this->rule = new \Floppy\Server\RequestHandler\Security\PolicyGuardRule(new ChecksumChecker(self::VALID_SIGNATURE));
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

        $this->rule->__invoke($request);
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

        $this->rule->__invoke($request);
    }

    /**
     * @test
     */
    public function givenValidSignatureAndPolicy_ok()
    {
        //given

        $request = $this->createRequest(self::VALID_SIGNATURE, $this->createValidPolicy());

        //when

        $this->rule->__invoke($request);
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
}
 