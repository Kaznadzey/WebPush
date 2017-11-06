<?php

namespace Nazz\WebPush\Sender\Client;

use Nazz\WebPush\Sender\Message;

/**
 * Class FirebaseXMPP
 */
class FirebaseXMPP implements SenderClientInterface
{
    /** @var string */
    private $host;

    /** @var int */
    private $port;

    /** @var string */
    private $apiKey;

    /** @var int */
    private $senderId;

    /** @var string */
    private $processId;

    /** @var resource */
    private $socketClient;

    /** @var string|null */
    private $openResponseTag;

    /** @var string|null */
    private $closeResponseTag;

    /**
     * @param string          $processId
     * @param int             $senderId
     * @param string          $apiKey
     * @param string          $host
     * @param int             $port
     */
    public function __construct(
        $processId, // unique process id
        $senderId, // firebase sender id
        $apiKey, // firebase api key
        $host, // host for firebase server (production or development)
        $port // port for firebase server (production or development)
    ) {
        $this->processId = $processId;
        $this->senderId  = $senderId;
        $this->apiKey    = $apiKey;
        $this->host      = $host;
        $this->port      = $port;
    }

    /**
     * @param string  $token
     * @param Message $message
     *
     * @return bool
     * @throws \DomainException
     * @throws \Exception
     */
    public function send($token, Message $message)
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
            'to'           => $token,
            'message_id'   => $message->getId(),
            'data'         => $pushMessage,
            'time_to_live' => 0,
        ];

        $params = json_encode($params);

        return $this->sendRequest($message->getId(), $params);
    }

    /**
     * @param string $messageId
     * @param string $encodedParams
     *
     * @return bool
     * @throws \DomainException
     * @throws \Exception
     */
    private function sendRequest($messageId, $encodedParams)
    {
        $sentSuccess  = false;
        $socketClient = $this->getSocketClient();
        $message = sprintf(
            '<message id="%s"><gcm xmlns="google:mobile:data">%s</gcm></message>',
            $messageId,
            $encodedParams
        );

        try {
            $sentLength = fwrite($socketClient, $message, strlen($message));

            if (is_bool($sentLength)) {
                return false;
            }

            while ($response = $this->readResponse($socketClient)) {

                $parsedResponse = $this->parseResponse($response);

                /** @var \DOMElement $childNode */
                foreach ($parsedResponse->childNodes as $childNode) {

                    if ($childNode->localName === 'message') {
                        $decodedNodeValue = json_decode($childNode->nodeValue);

                        if (property_exists($decodedNodeValue, 'message_type')) {

                            if ($decodedNodeValue->message_type === 'ack') {
                                $sentSuccess = true;

                                break 2;
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }
        return $sentSuccess;
    }

    /**
     * @return resource
     * @throws \DomainException
     */
    private function getSocketClient()
    {
        if (!$this->isSocketClientOpened()) {
            $this->socketClient = $this->openSocketClient();

            if (!$this->isSocketClientOpened()) {
                throw new \DomainException('Socket client open error.');
            }
        }

        return $this->socketClient;
    }

    /**
     * @return bool
     */
    private function isSocketClientOpened()
    {
        if (is_resource($this->socketClient) && !feof($this->socketClient)) {
            return true;
        }

        return false;
    }

    /**
     * @return null|resource
     * @throws \DomainException
     */
    private function openSocketClient()
    {
        $errorNumber = null;
        $errorString = null;
        $context     = stream_context_create(
            [
                'ssl' => [
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ]
        );
        $socketClient = stream_socket_client(
            sprintf(
                "tls://%s:%u",
                $this->host,
                $this->port
            ),
            $errorNumber,
            $errorString,
            3,
            STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT,
            $context
        );

        if (is_bool($socketClient)) {
            throw new \DomainException('Can\'t connect to socket client.');
        }
        stream_set_blocking($socketClient, true);
        fwrite(
            $socketClient,
            sprintf(
                '<stream:stream to="%s" version="1.0" xmlns="jabber:client" xmlns:stream="http://etherx.jabber.org/streams">',
                $this->host
            )
        );
        try {
            $isConnected = false;

            while ($response = $this->readResponse($socketClient)) {
                $domDocument = $this->parseResponse($response);

                /** @var \DOMElement $childNode */
                foreach ($domDocument->childNodes as $childNode) {

                    if ($childNode->localName === 'features') {

                        /** @var \DOMElement $subChildNode */
                        foreach ($childNode->childNodes as $subChildNode) {

                            if ($subChildNode->localName === 'mechanisms') {
                                fwrite(
                                    $socketClient,
                                    sprintf(
                                        '<auth mechanism="PLAIN" xmlns="urn:ietf:params:xml:ns:xmpp-sasl">%s</auth>',
                                        base64_encode(
                                            chr(0)
                                            . $this->senderId
                                            . '@gcm.googleapis.com'
                                            . chr(0)
                                            . $this->apiKey
                                        )
                                    )
                                );
                            } elseif ($subChildNode->localName === 'bind') {
                                fwrite(
                                    $socketClient,
                                    sprintf(
                                        '<iq to="%s" type="set" id="%s">
                                                    <bind xmlns="urn:ietf:params:xml:ns:xmpp-bind">
                                                        <resource>test</resource>
                                                    </bind>
                                                </iq>',
                                        $this->host,
                                        $this->processId . '-1'
                                    )
                                );
                            } elseif ($subChildNode->localName === 'session') {
                                fwrite(
                                    $socketClient,
                                    sprintf(
                                        '<iq to="%s" type="set" id="%s">
                                                    <session xmlns="urn:ietf:params:xml:ns:xmpp-session"/>
                                                </iq>',
                                        $this->host,
                                        $this->processId . '-2'
                                    )
                                );
                            }
                        }
                    } elseif ($childNode->localName === 'success') {
                        fwrite(
                            $socketClient,
                            sprintf(
                                '<stream:stream to="%s" version="1.0" xmlns="jabber:client" xmlns:stream="http://etherx.jabber.org/streams">',
                                $this->host
                            )
                        );
                    } elseif ($childNode->localName === 'failure') {
                        throw new \DomainException('Remote server block open socket request.');
                    } elseif ($childNode->localName === 'iq' && $childNode->getAttribute('type') === 'result') {

                        if ($childNode->getAttribute('id') == $this->processId . '-1') {
                            $isConnected = true;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            throw new \DomainException($e->getMessage());
        }

        if (!$isConnected && is_resource($socketClient)) {
            stream_set_blocking($socketClient, false);
            fclose($socketClient);
            $socketClient = null;
        }

        return $socketClient;
    }

    /**
     * @param $socketClient
     *
     * @return string
     * @throws \DomainException
     */
    private function readResponse($socketClient)
    {
        $response = fread($socketClient, 1387);

        if (is_bool($response)) {
            throw new \DomainException('Can\'t read response');
        }

        return trim($response);
    }

    /**
     * @param string $response
     *
     * @return \DOMElement
     * @throws \DomainException
     */
    private function parseResponse($response)
    {
        $dom          = new \DOMDocument();
        $dom->recover = true;
        $responseLoadedSuccess = $dom->loadXML($response, LIBXML_NOWARNING | LIBXML_NOERROR);
        $responseLoadedSuccessWithAdditionalTags = false;

        if ($responseLoadedSuccess
            && $dom->documentElement->localName !== 'stream'
            && is_string($this->openResponseTag)
            && is_string($this->closeResponseTag)
        ) {
            $responseLoadedSuccessWithAdditionalTags = $dom->loadXML(
                sprintf(
                    '%s%s%s',
                    $this->openResponseTag,
                    $response,
                    $this->closeResponseTag
                ),
                LIBXML_NOWARNING | LIBXML_NOERROR
            );
        }

        if (!$responseLoadedSuccess && !$responseLoadedSuccessWithAdditionalTags) {
            throw new \DomainException('Can\'t load response as DMOElement');
        }

        if (!is_null($dom->documentElement)
            && $dom->documentElement->localName === 'stream'
        ) {

            if (!$responseLoadedSuccessWithAdditionalTags) {
                $this->openResponseTag  = substr($response, 0, strpos($response, '>') + 1);
                $this->closeResponseTag = sprintf(
                    '</%s>',
                    $dom->documentElement->tagName
                );
            }

            return $dom->documentElement;
        }

        throw new \DomainException('Response is Unparsible');
    }
}
