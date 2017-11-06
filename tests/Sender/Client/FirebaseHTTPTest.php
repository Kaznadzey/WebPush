<?php

namespace Nazz\WebPush\Sender\Client;

use Nazz\WebPush\Http\Request;
use Nazz\WebPush\Sender\Message;

/**
 * Class FirebaseHTTPTest
 */
class FirebaseHTTPTest extends \PHPUnit_Framework_TestCase
{
    public function testSend()
    {
        $message = new Message(
            bin2hex(random_bytes(16)),
            'MessageTitle',
            'MessageBody',
            'http://www.google.com',
            'http://www.google.com',
            32
        );

        $response          = new \stdClass();
        $response->success = 1;
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'sendPost'
                ]
            )
            ->getMock();

        $request->method('sendPost')->willReturn($response);

        /** @var Request $request */

        $client = new FirebaseHTTP($request, 'http://google.com', 'api-key');

        $result = $client->send('test-token', $message);

        $this->assertTrue($result);
    }
}
