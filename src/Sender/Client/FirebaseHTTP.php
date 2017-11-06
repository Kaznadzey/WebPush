<?php

namespace Nazz\WebPush\Sender\Client;

use Nazz\WebPush\Http\Request;
use Nazz\WebPush\Sender\Message;

/**
 * Class FirebaseHTTP
 */
class FirebaseHTTP implements SenderClientInterface
{
    /** @var Request */
    private $request;

    /** @var string */
    private $apiUrl;

    /** @var string */
    private $apiKey;

    /**
     * FirebaseHTTP constructor.
     *
     * @param Request $request
     * @param string  $apiUrl
     * @param string  $apiKey
     */
    public function __construct(Request $request, $apiUrl, $apiKey)
    {
        $this->request = $request;
        $this->apiUrl  = $apiUrl;
        $this->apiKey  = $apiKey;
    }

    /**
     * @param string  $token
     * @param Message $message
     *
     * @return bool
     */
    public function send($token, Message $message)
    {
        $response = $this->request->sendPost(
            $this->apiUrl,
            $this->getHeaders(),
            $this->getMessageToSend($token, $message)
        );

        if (is_object($response)
            && property_exists($response, 'success')
            && $response->success !== 0
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    protected function getHeaders()
    {
        return [
            'Authorization: key=' . $this->apiKey,
            'Content-Type: application/json',
        ];
    }

    /**
     * @param string  $token
     * @param Message $message
     *
     * @return string
     */
    protected function getMessageToSend($token, Message $message)
    {
        $pushMessage = [
            'notification' => [
                'title' => $message->getTitle(),
                'body'  => $message->getBody(),
                'icon'  => $message->getIcon(),
                'url'   => $message->getUrl(),
            ],
        ];

        $params = [
            'token'        => [
                $token,
            ],
            'data'         => $pushMessage,
            'time_to_live' => $message->getTtl(),
        ];

        return json_encode($params);
    }
}
