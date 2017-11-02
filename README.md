WebPush library
=========
[![Build Status](https://travis-ci.org/Kaznadzey/WebPush.svg?branch=master)](https://travis-ci.org/Kaznadzey/WebPush)
[![Total Downloads](https://poser.pugx.org/nazz/webpush/downloads)](https://packagist.org/packages/nazz/webpush)
[![License](https://poser.pugx.org/nazz/webpush/license)](https://packagist.org/packages/nazz/webpush)

Installation
------------

You can install library through Composer:
```json
{
    "require": {
        "sokil/php-isocodes": "dev-master"
    }
}
```

Or use composer command:

```console
composer require nazz/webpush
```


Usage
------------

```php
<?php

    $client = new \Nazz\WebPush\Sender\Client\APN(
        '',
        123456789,
        '',
        30
    );

    $message = new \Nazz\WebPush\Sender\Message(
        md5(microtime(true)),
        'Message title',
        'Message body',
        'https://firebase.google.com/_static/79b4008122/images/firebase/lockup.png',
        'https://firebase.google.com'
    );
    
    $client->send('token', $message);
```

This code is general for all webPush clients.