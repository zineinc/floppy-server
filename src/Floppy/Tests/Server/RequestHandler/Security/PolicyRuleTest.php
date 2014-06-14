<?php


namespace Floppy\Tests\Server\RequestHandler\Security;


use Floppy\Common\AttributesBag;
use Floppy\Common\FileHandler\PathMatchingException;
use Floppy\Common\FileId;
use Floppy\Common\FileSource;
use Floppy\Common\FileType;
use Floppy\Common\HasFileInfo;
use Floppy\Server\FileHandler\FileHandler;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\HttpFoundation\Request;
use Floppy\Server\RequestHandler\Security\PolicyRule;
use Floppy\Tests\Common\Stub\ChecksumChecker;
use Floppy\Tests\Server\RequestHandler\Security\ZineInc;
use Floppy\Common\Stream\StringInputStream;
use Symfony\Component\HttpFoundation\Response;

class PolicyRuleTest extends \PHPUnit_Framework_TestCase
{
    const ID = 'id';

    const VALID_FILE_HANDLER = 'image';
    const INVALID_FILE_HANDLER = 'file';

    const VALID_FILE_EXT = 'jpg';
    const INVALID_FILE_EXT = 'exe';

    const VALID_SIGNATURE = 'valid-signature';
    const INVALID_SIGNATURE = 'invalid-signature';

    /**
     * @test
     * @expectedException \Floppy\Server\RequestHandler\Exception\AccessDeniedException
     */
    public function givenInvalidSignature_throwEx()
    {
        //given

        $request = $this->createRequest(self::INVALID_SIGNATURE, $this->createValidPolicy());

        //when

        $this->invokeRule($request, new PolicyRuleTest_FileInfo());
    }

    /**
     * @test
     */
    public function givenRuleWithAllowedMissingPolicy_givenRequestWithoutPolicy_ok()
    {
        //given

        $request = new Request();

        //when

        $object = $this->createRule(true)->processRule($request, new FileId('id'));
        $this->assertNotNull($object);
    }

    /**
     * @test
     * @expectedException \Floppy\Server\RequestHandler\Exception\AccessDeniedException
     */
    public function givenRuleWithPolicyRequired_givenRequestWithoutPolicy_throwEx()
    {
        //given

        $request = new Request();

        //when

        $this->createRule(false)->processRule($request, new FileId('id'));
    }

    /**
     * @test
     * @expectedException \Floppy\Server\RequestHandler\Exception\AccessDeniedException
     */
    public function givenPolicyExpired_throwEx()
    {
        //given

        $request = $this->createRequest(self::VALID_SIGNATURE, $this->createExpiredPolicy());

        //when

        $this->invokeRule($request, new PolicyRuleTest_FileInfo());
    }

    /**
     * @test
     * @dataProvider booleanProvider
     */
    public function givenValidSignatureAndPolicy_ok($policyInPostVariables)
    {
        //given

        $request = $this->createRequest(self::VALID_SIGNATURE, $this->createValidPolicy(), $policyInPostVariables);

        //when

        $this->invokeRule($request, new PolicyRuleTest_FileInfo());
    }

    public function booleanProvider()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * @test
     * @expectedException \Floppy\Server\RequestHandler\Exception\ValidationException
     */
    public function givenFileTypeThatIsMissingInPolicy_throwEx()
    {
        //given

        $request = $this->createRequest(self::VALID_SIGNATURE, $this->createValidPolicyWithFileType(self::VALID_FILE_HANDLER));

        //when

        $this->invokeRule($request, $this->createFileSource(self::INVALID_FILE_EXT));
    }

    /**
     * @test
     */
    public function givenFileTypesThatIsInPolicy_ok()
    {
        //given

        $request = $this->createRequest(self::VALID_SIGNATURE, $this->createValidPolicyWithFileType(self::VALID_FILE_HANDLER));

        //when

        $this->invokeRule($request, $this->createFileSource(self::VALID_FILE_EXT));
    }

    /**
     * @test
     */
    public function givenPolicyWithoutFileTypes_passEveryFile()
    {
        //given

        $request = $this->createRequest(self::VALID_SIGNATURE, $this->createValidPolicy());

        //when

        $this->invokeRule($request, $this->createFileSource(self::VALID_FILE_EXT));
    }

    /**
     * @test
     */
    public function givenPolicyWithAccess_addAccessInfoToObject()
    {
        //given

        $request = $this->createRequest(self::VALID_SIGNATURE, $this->createValidPolicyWith(array(
            'access' => 'private',
        )));

        //when

        $object = $this->invokeRule($request, $this->createFileSource(self::VALID_FILE_EXT));

        $this->assertEquals('private', $object->info()->get('access'));
    }

    private function createRequest($signature, $policy, $policyInPostVariables = true)
    {
        $request = new Request();
        $bag = $policyInPostVariables ? $request->request : $request->query;
        $bag->set('signature', $signature);
        $bag->set('policy', $policy);

        return $request;
    }

    private function createValidPolicy()
    {
        return $this->createValidPolicyWith(array());
    }

    private function createValidPolicyWith(array $policy)
    {
        return base64_encode(json_encode(array(
            'expiration' => time() + 60*5,
        ) + $policy));
    }

    private function createValidPolicyWithFileType($fileType)
    {
        return base64_encode(json_encode(array(
            'expiration' => time() + 60*5,
            'file_types' => array($fileType),
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
    protected function invokeRule($request, HasFileInfo $object)
    {
        $actualObject = $this->createRule()->processRule($request, $object);

        $this->assertNotNull($actualObject);

        return $actualObject;
    }

    private function createFileSource($ext)
    {
        return new FileSource(new StringInputStream(''), new FileType('', $ext));
    }

    /**
     * @return PolicyRule
     */
    private function createRule($policyMissingAllowed = false)
    {
        return new \Floppy\Server\RequestHandler\Security\PolicyRule(new ChecksumChecker(self::VALID_SIGNATURE), array(
            self::VALID_FILE_HANDLER => new PolicyRuleTest_FileHandler(self::VALID_FILE_EXT),
            self::INVALID_FILE_HANDLER => new PolicyRuleTest_FileHandler(self::INVALID_FILE_EXT),
        ), $policyMissingAllowed);
    }
}

class PolicyRuleTest_FileHandler implements FileHandler
{
    private $validExtension;

    public function __construct($validExtension)
    {
        $this->validExtension = $validExtension;
    }


    public function supports(FileType $fileType)
    {
        return $fileType->extension() === $this->validExtension;
    }

    public function getStoreAttributes(FileSource $file)
    {
        return array();
    }

    public function beforeStoreProcess(FileSource $file)
    {
        return $file;
    }

    public function beforeSendProcess(FileSource $file, FileId $fileId)
    {
        return $file;
    }

    public function match($variantFilepath)
    {
        throw new \BadMethodCallException();
    }

    public function matches($variantFilepath)
    {
        throw new \BadMethodCallException();
    }

    public function filterResponse(Response $response, FileSource $fileSource, FileId $fileId)
    {
        throw new \BadMethodCallException();
    }
}

class PolicyRuleTest_FileInfo implements HasFileInfo
{
    public function info()
    {
        return new AttributesBag();
    }

    public function withInfo(array $info)
    {
        return $this;
    }
}
 