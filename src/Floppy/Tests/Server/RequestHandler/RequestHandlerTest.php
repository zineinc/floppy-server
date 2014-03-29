<?php


namespace Floppy\Tests\Server\RequestHandler;


use Floppy\Common\ErrorCodes;
use Floppy\Common\StorageError;
use Floppy\Common\StorageException;
use Floppy\Server\RequestHandler\Action\Action;
use Floppy\Server\RequestHandler\ActionResolver;
use Floppy\Server\RequestHandler\RequestHandler;
use Floppy\Tests\Server\Stub\FirewallStub;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $requestHandler;

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function actionThrewException_translateExceptionToResponse($exception, $expectedResponseStatusCode)
    {
        //given

        $action = $this->getMock('Floppy\Server\RequestHandler\Action\Action');
        $requestHandler = $this->createRequestHandler($action);

        $action->expects($this->once())
            ->method('execute')
            ->will($this->throwException($exception));

        //when

        $response = $requestHandler->handle(new Request());

        //then

        $this->verifyMockObjects();
        $this->assertEquals($expectedResponseStatusCode, $response->getStatusCode());
    }

    public function dataProvider()
    {
        return array(
            array(
                new RequestHandlerTest_StorageException(null, ErrorCodes::ACCESS_DENIED), 401
            ),
            array(
                new RequestHandlerTest_StorageException(null, ErrorCodes::FILE_NOT_FOUND), 404
            ),
            array(
                new RequestHandlerTest_StorageException(null, ErrorCodes::INVALID_CHECKSUM), 400
            ),
            array(
                new RequestHandlerTest_StorageError(), 500
            ),
        );
    }

    /**
     * @test
     */
    public function actionReturnedResponse_returnTheResponse()
    {
        //given

        $action = $this->getMock('Floppy\Server\RequestHandler\Action\Action');
        $requestHandler = $this->createRequestHandler($action);

        $response = new Response('content');

        $action->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($response));

        //when

        $actualResponse = $requestHandler->handle(new Request());

        //then

        $this->verifyMockObjects();
        $this->assertEquals($response, $actualResponse);
    }

    /**
     * @param $action
     * @return RequestHandler
     */
    protected function createRequestHandler($action)
    {
        $requestHandler = new RequestHandler(new RequestHandlerTest_ActionResolver($action), new FirewallStub());
        return $requestHandler;
    }
}

class RequestHandlerTest_ActionResolver implements ActionResolver
{
    private $action;

    public function __construct($action)
    {
        $this->action = $action;
    }

    /**
     * @param Request $request
     * @return Action
     */
    public function resolveAction(Request $request)
    {
        return $this->action;
    }
}

class RequestHandlerTest_StorageException extends \Exception implements StorageException
{
}

class RequestHandlerTest_StorageError extends \Exception implements StorageError
{
}
 