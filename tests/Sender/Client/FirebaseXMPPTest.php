<?php

namespace Nazz\WebPush\Sender\Client;

use Nazz\WebPush\Sender\Message;

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
}
