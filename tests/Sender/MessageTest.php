<?php

namespace Nazz\WebPush\Sender;

/**
 * Class MessageTest
 */
class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function testMessage()
    {
        $id    = 123;
        $title = 'Title';
        $body  = 'Body';
        $icon  = 'http://www.google.com';
        $url   = 'http://www.google.com';
        $ttl   = 345;

        $message = new Message(
            $id,
            $title,
            $body,
            $icon,
            $url,
            $ttl
        );

        $this->assertEquals($message->getId(), $id);
        $this->assertEquals($message->getTitle(), $title);
        $this->assertEquals($message->getBody(), $body);
        $this->assertEquals($message->getIcon(), $icon);
        $this->assertEquals($message->getUrl(), $url);
        $this->assertEquals($message->getTtl(), $ttl);
    }
}
