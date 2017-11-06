<?php

namespace Nazz\WebPush\Sender\Client;

use Nazz\WebPush\Http\Request;
use Nazz\WebPush\Sender\Message;

/**
 * Class FirebaseHTTPTest
 */
class FirebaseHTTPTest extends \PHPUnit_Framework_TestCase
{
    public function testSendSuccess()
    {
        $message = new Message(
            md5('test'),
            'MessageTitle',
            'MessageBody',
            'http://www.google.com',
            'http://www.google.com',
            32
        );

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

        $result = $client->send('test-token', $message);

        $this->assertTrue($result);
    }

    public function testSendError()
    {
        $message = new Message(
            md5('test'),
            'MessageTitle',
            'MessageBody',
            'http://www.google.com',
            'http://www.google.com',
            32
        );

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

        $result = $client->send('test-token', $message);

        $this->assertTrue($result);
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
        $message = new Message(
            md5('test'),
            'MessageTitle',
            'MessageBody',
            'http://www.google.com',
            'http://www.google.com',
            32
        );

        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Request $request */

        $client = new FirebaseHTTP($request, 'http://google.com', 'api-key');

        $jsonMessage = $this->getMethod('getMessageToSend')
            ->invoke($client, 'token', $message);

        $pushMessage = [
            'notification' => [
                'title' => $message->getTitle(),
                'body'  => $message->getBody(),
                'icon'  => $message->getIcon(),
                'url'   => $message->getUrl(),
            ],
        ];

        $params = [
            'token'        => [
                'token',
            ],
            'data'         => $pushMessage,
            'time_to_live' => $message->getTtl(),
        ];

        $this->assertTrue(is_string($jsonMessage));
        $this->assertTrue(is_array(json_decode($jsonMessage, true)));
        $this->assertEquals(
            json_encode($params),
            $jsonMessage
        );
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
