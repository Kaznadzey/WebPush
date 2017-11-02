<?php

require 'loader.php';

use Nazz\WebPush\Sender\Client\FirebaseHTTP;
use Nazz\WebPush\Sender\Message;

try {
    $client = new FirebaseHTTP(
        'fcm-xmpp.googleapis.com',
        'my-firebase-api-key'
    );

    $message = new Message(
        md5(microtime(true)),
        'Message title',
        'Message body',
        'https://firebase.google.com/_static/79b4008122/images/firebase/lockup.png',
        'https://firebase.google.com'
    );

    var_dump($client->send('This is subscription token', $message));
} catch (\Throwable $e) {
    echo PHP_EOL . $e->getMessage() . PHP_EOL;
}

?>