<?php

namespace Nazz\WebPush\Sender\Client;

use Nazz\WebPush\Sender\Message;

/**
 * Interface ClientInterface
 */
interface ClientInterface
{
    /**
     * @param string  $token
     * @param Message $message
     *
     * @return bool
     */
    public function send($token, Message $message);
}
