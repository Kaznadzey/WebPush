<?php

namespace Nazz\WebPush\Sender\Client;

use Nazz\WebPush\Sender\Message;

function fread($actualResponse)
{
    return $actualResponse;
}

/**
 * Class FirebaseXMPPTest
 */
class FirebaseXMPPTest extends \PHPUnit_Framework_TestCase
{
    /** @var Message|null */
    private $message;

    public function testGetMessageToSend()
    {
        $client = new FirebaseXMPP(
            'id',
            123456,
            'apiKey',
            'host',
            12
        );

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
            'to'           => 'token',
            'message_id'   => $this->getMessage()->getId(),
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

    public function testParseResponseSuccessCaseFirstResponse()
    {
        $client = new FirebaseXMPP(
            'id',
            123456,
            'apiKey',
            'host',
            12
        );

        $response = '<stream><node/></stream>';

        $domElement = $this->getMethod('parseResponse')
            ->invoke($client, $response);

        $this->assertInstanceOf(\DOMElement::class, $domElement);
    }

    public function testParseResponseSuccessCaseSecondResponse()
    {
        $client = new FirebaseXMPP(
            'id',
            123456,
            'apiKey',
            'host',
            12
        );

        $this->getProperty('openResponseTag')->setValue($client, '<stream>');
        $this->getProperty('closeResponseTag')->setValue($client, '</stream>');

        $response = '<node/>';

        $domElement = $this->getMethod('parseResponse')
            ->invoke($client, $response);

        $this->assertInstanceOf(\DOMElement::class, $domElement);
    }

    public function testParseResponseFail()
    {
        $client = new FirebaseXMPP(
            'id',
            123456,
            'apiKey',
            'host',
            12
        );

        $response = '<root><node/></root>';

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Response is Unparsible');
        $this->getMethod('parseResponse')->invoke($client, $response);
    }

    public function testReadResponse()
    {
        $client = new FirebaseXMPP(
            'id',
            123456,
            'apiKey',
            'host',
            12
        );

        $expectedResponse = '<stream><node/></stream>';

        $actualResponse = $this->getMethod('readResponse')
            ->invoke($client, $expectedResponse);

        $this->assertEquals($expectedResponse, $actualResponse);
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
        $class  = new \ReflectionClass(FirebaseXMPP::class);
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
        $class    = new \ReflectionClass(FirebaseXMPP::class);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);

        return $property;
    }
}
