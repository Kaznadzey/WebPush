<?php

namespace Nazz\WebPush\Sender\Client;

use Nazz\WebPush\Sender\Message;

function is_resource($param)
{
    return $param;
}

function fwrite($client, $message, $length)
{
    return true;
}

function stream_socket_client()
{
    return false;
}

/**
 * Class APNTest
 */
class APNTest extends \PHPUnit_Framework_TestCase
{
    /** @var Message|null */
    private $message;

    public function testCreateBinaryMessage()
    {
        $client = new APN(
            '',
            '',
            '',
            300
        );

        $actualResponse = $this->getMethod('createBinaryMessage')
            ->invoke($client, md5('token'), $this->getMessage());

        $payloadMessage = [
            'aps' => [
                'alert'    => [
                    'title'  => $this->getMessage()->getTitle(),
                    'body'   => $this->getMessage()->getBody(),
                    'action' => APN::SHOW_BUTTON,
                ],
                'url-args' => [
                    'arguments',
                ],
            ],
        ];

        $body = json_encode($payloadMessage);

        $expectedResponse = chr(0) .
            chr(0) .
            chr(32) .
            pack('H*', md5('token')) .
            chr(0) . chr(strlen($body)) .
            $body;

        $this->assertEquals(
            $expectedResponse,
            $actualResponse
        );
    }

    public function testGetClientUrlPart()
    {
        $client = new APN(
            '',
            '',
            '',
            300
        );

        $actualResponse = $this->getMethod('getClientUrlPart')
            ->invoke($client, $this->getMessage());

        $expectedResponse = 'arguments';

        $this->assertEquals(
            $expectedResponse,
            $actualResponse
        );
    }

    public function testGetSocketClientSuccess()
    {
        $client = new APN(
            '',
            '',
            '',
            300
        );

        $this->getProperty('socketClient')->setValue($client, true);

        $actualResponse = $this->getMethod('getSocketClient')
            ->invoke($client);

        $this->assertTrue($actualResponse);
    }

    public function testGetSocketClientFail()
    {
        $client = new APN(
            '',
            '',
            '',
            300
        );

        $this->getProperty('socketClient')->setValue($client, false);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cant\'t create socket client. Error 0: .');

        $this->getMethod('getSocketClient')
            ->invoke($client);
    }

    public function testSend()
    {
        $client = new APN(
            '',
            '',
            '',
            300
        );

        $this->getProperty('socketClient')->setValue($client, true);

        $isSent = $client->send(md5('token'), $this->getMessage());

        $this->assertTrue($isSent);
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
                'http://www.google.com/arguments',
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
        $class  = new \ReflectionClass(APN::class);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @param $propertyName
     *
     * @return \ReflectionProperty
     */
    protected function getProperty($propertyName)
    {
        $class    = new \ReflectionClass(APN::class);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);

        return $property;
    }
}
