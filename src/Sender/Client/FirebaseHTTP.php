<?php

namespace Nazz\WebPush\Sender\Client;

use Nazz\WebPush\Sender\Message;

/**
 * Class FirebaseHTTP
 */
class FirebaseHTTP implements ClientInterface
{
    /**
     * @param string  $token
     * @param Message $message
     *
     * @return bool
     */
    public function send($token, Message $message)
    {

    }
}