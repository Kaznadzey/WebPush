<?php

namespace Nazz\WebPush\Sender\Client;

use Nazz\WebPush\Http\Request;
use Nazz\WebPush\Sender\Message;

/**
 * Class FirebaseHTTPTest
 */
class FirebaseHTTPTest extends \PHPUnit_Framework_TestCase
{
    /** @var Message|null */
    private $message;

    public function testSendSuccess()
    {
        $response          = new \stdClass();
        $response->success = 1;
        $request           = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'sendPost',
                ]
            )
            ->getMock();

        $request->method('sendPost')->willReturn($response);

        /** @var Request $request */

        $client = new FirebaseHTTP($request, 'http://google.com', 'api-key');

        $result = $client->send('test-token', $this->getMessage());

        $this->assertTrue($result);
    }

    public function testSendError()
    {
        $request           = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'sendPost',
                ]
            )
            ->getMock();

        $request->method('sendPost')->willReturn(null);

        /** @var Request $request */

        $client = new FirebaseHTTP($request, 'http://google.com', 'api-key');

        $result = $client->send('test-token', $this->getMessage());

        $this->assertFalse($result);
    }

    public function testGetHeaders()
    {
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Request $request */

        $client = new FirebaseHTTP($request, 'http://google.com', 'api-key');

        $actualResult = $this->getMethod('getHeaders')->invoke($client);

        $this->assertTrue(is_array($actualResult));
        $this->assertEquals(
            [
                'Authorization: key=api-key',
                'Content-Type: application/json',
            ],
            $actualResult
        );
    }

    public function testGetMessageToSend()
    {
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Request $request */

        $client = new FirebaseHTTP($request, 'http://google.com', 'api-key');

        $jsonMessage = $this->getMethod('getMessageToSend')
            ->invoke($client, 'token', $this->getMessage());

        $pushMessage = [
            'notification' => [
                'title' => $this->getMessage()->getTitle(),
                'body'  => $this->getMessage()->getBody(),
                'icon'  => $this->getMessage()->getIcon(),
                'url'   => $this->getMessage()->getUrl(),
            ],
        ];

        $params = [
            'token'        => [
                'token',
            ],
            'data'         => $pushMessage,
            'time_to_live' => $this->getMessage()->getTtl(),
        ];

        $this->assertTrue(is_string($jsonMessage));
        $this->assertTrue(is_array(json_decode($jsonMessage, true)));
        $this->assertEquals(
            json_encode($params),
            $jsonMessage
        );
    }

    /**
     * @return Message
     */
    protected function getMessage()
    {
        if (is_null($this->message)) {
            $this->message = new Message(
                md5('test'),
                'MessageTitle',
                'MessageBody',
                'http://www.google.com',
                'http://www.google.com',
                32
            );
        }

        return $this->message;
    }

    /**
     * @param string $methodName
     *
     * @return \ReflectionMethod
     */
    protected function getMethod($methodName)
    {
        $class  = new \ReflectionClass(FirebaseHTTP::class);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }
}
