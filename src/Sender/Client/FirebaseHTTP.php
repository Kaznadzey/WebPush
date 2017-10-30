<?php

namespace Nazz\WebPush\Sender\Client;

use Nazz\WebPush\Sender\Message;

/**
 * Class FirebaseHTTP
 */
class FirebaseHTTP implements SenderClientInterface
{
    /** @var string */
    private $apiUrl;

    /** @var string */
    private $apiKey;

    /**
     * FirebaseHTTP constructor.
     *
     * @param string $apiUrl
     * @param string $apiKey
     */
    public function __construct($apiUrl, $apiKey)
    {
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
    }

    /**
     * @param string  $token
     * @param Message $message
     *
     * @return bool
     */
    public function send($token, Message $message)
    {
        $message = [
            'notification' => [
                'title' => $message->getTitle(),
                'body'  => $message->getBody(),
                'icon'  => $message->getIcon(),
                'url'   => $message->getUrl(),
            ],
        ];

        $params = [
            '$token'       => [
                $token,
            ],
            'data'         => $message,
            'time_to_live' => 0,
        ];
        $params = json_encode($params);

        $headers = [
            'Authorization: key=' . $this->apiKey,
            'Content-Type: application/json',
        ];

        $result = json_decode($this->sendPostRequest($this->apiUrl, $headers, $params));

        if ($result->success !== 0) {
            return true;
        }

        return false;
    }

    /**
     * @param string $url
     * @param array  $headers
     * @param string $encodedParams
     *
     * @return string
     */
    private function sendPostRequest($url, array $headers, $encodedParams)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedParams);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}
