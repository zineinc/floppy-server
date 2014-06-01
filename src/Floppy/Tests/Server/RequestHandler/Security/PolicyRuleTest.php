<?php


namespace Floppy\Tests\Server\RequestHandler\Security;


use Floppy\Common\AttributesBag;
use Floppy\Common\FileHandler\PathMatchingException;
use Floppy\Common\FileId;
use Floppy\Common\FileSource;
use Floppy\Common\FileType;
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
    const VALID_FILE_HANDLER = 'image';
    const INVALID_FILE_HANDLER = 'file';

    const VALID_FILE_EXT = 'jpg';
    const INVALID_FILE_EXT = 'exe';

    const VALID_SIGNATURE = 'valid-signature';
    const INVALID_SIGNATURE = 'invalid-signature';

    private $rule;

    protected function setUp()
    {
        $this->rule = new \Floppy\Server\RequestHandler\Security\PolicyRule(new ChecksumChecker(self::VALID_SIGNATURE), array(
            self::VALID_FILE_HANDLER => new PolicyRuleTest_FileHandler(self::VALID_FILE_EXT),
            self::INVALID_FILE_HANDLER => new PolicyRuleTest_FileHandler(self::INVALID_FILE_EXT),
        ));
    }

    /**
     * @test
     * @expectedException Floppy\Server\RequestHandler\Exception\AccessDeniedException
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
     * @expectedException Floppy\Server\RequestHandler\Exception\AccessDeniedException
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
     * @dataProvider booleanProvider
     */
    public function givenValidSignatureAndPolicy_ok($policyInPostVariables)
    {
        //given

        $request = $this->createRequest(self::VALID_SIGNATURE, $this->createValidPolicy(), $policyInPostVariables);

        //when

        $this->invokeRule($request);
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
        return base64_encode(json_encode(array(
            'expiration' => time() + 60*5,
        )));
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
    protected function invokeRule($request, $object = null)
    {
        $this->rule->checkRule($request, $object);
    }

    private function createFileSource($ext)
    {
        return new FileSource(new StringInputStream(''), new FileType('', $ext));
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
 