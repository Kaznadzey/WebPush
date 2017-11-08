<?php

namespace Nazz\WebPush\Sender\Client;

use Nazz\WebPush\Sender\Message;

/**
 * Class APN
 */
class APN implements SenderClientInterface
{
    const SHOW_BUTTON = 'Show';

    const MESSAGE_LENGTH_LIMIT = 256;

    /** @var string */
    private $pemCertificatePath;

    /** @var string */
    private $pemCertificatePassword;

    /** @var string */
    private $apnServerAddress;

    /** @var int */
    private $timeout;

    /** @var resource */
    private $socketClient;

    /**
     * @param string $pemCertificatePath
     * @param string $pemCertificatePassword
     * @param string $apnServerAddress
     * @param int    $timeout
     */
    public function __construct(
        $pemCertificatePath,
        $pemCertificatePassword,
        $apnServerAddress,
        $timeout
    ) {
        $this->pemCertificatePath     = $pemCertificatePath;
        $this->pemCertificatePassword = $pemCertificatePassword;
        $this->apnServerAddress       = $apnServerAddress;
        $this->timeout                = $timeout;
    }

    /**
     * @param string  $token This is registration id received from APN service during subscription
     * @param Message $message
     * @param bool    $resendOnError
     *
     * @return bool
     * @throws \DomainException
     * @codeCoverageIgnore
     */
    public function send($token, Message $message, $resendOnError = true)
    {
        $binaryMessage = $this->createBinaryMessage($token, $message);
        $messageLength = strlen($binaryMessage);
        $socketClient  = $this->getSocketClient();

        if ($messageLength > self::MESSAGE_LENGTH_LIMIT) {
            throw new \DomainException(
                sprintf(
                    'Message length is %s, but length limit is %s',
                    $messageLength,
                    self::MESSAGE_LENGTH_LIMIT
                )
            );
        }

        if (!is_resource($socketClient)) {
            throw new \DomainException(
                sprintf(
                    'Can\'t create socket client.'
                )
            );
        }

        try {
            $messageSent = fwrite($socketClient, $binaryMessage, $messageLength);
        } catch (\Exception $e) {
            if (!$resendOnError) {
                $this->closeSocketConnection();
                usleep(100000);

                return $this->send($token, $message, false);
            } else {
                $messageSent = false;
            }
        }

        $sentResult = true;
        if (is_bool($messageSent) && $messageSent === false) {
            $sentResult = false;
        }

        return $sentResult;
    }

    /**
     * @param string  $token
     * @param Message $message
     *
     * @return string
     */
    protected function createBinaryMessage($token, Message $message)
    {
        $payloadMessage = [
            'aps' => [
                'alert'    => [
                    'title'  => $message->getTitle(),
                    'body'   => $message->getBody(),
                    'action' => self::SHOW_BUTTON,
                ],
                'url-args' => [
                    $this->getClientUrlPart($message),
                ],
            ],
        ];

        $body = json_encode($payloadMessage);

        $binaryMessage = chr(0) .
            chr(0) .
            chr(32) .
            pack('H*', $token) .
            chr(0) . chr(strlen($body)) .
            $body;

        return $binaryMessage;
    }

    /**
     * @param Message $message
     *
     * @return string
     */
    protected function getClientUrlPart(Message $message)
    {
        $url         = $message->getUrl();
        $explodedUrl = array_reverse(explode('/', $url));
        $clientUrl   = array_shift($explodedUrl);

        return $clientUrl;
    }

    /**
     * @return resource
     * @throws \DomainException
     */
    protected function getSocketClient()
    {
        if (!is_resource($this->socketClient)) {
            $this->socketClient = $this->openSocketClient();
        }

        return $this->socketClient;
    }

    /**
     * Close socket connection
     *
     * @codeCoverageIgnore
     */
    private function closeSocketConnection()
    {
        if (is_resource($this->socketClient)) {
            fclose($this->socketClient);
        }

        $this->socketClient = null;
    }

    /**
     * @return bool|resource
     * @throws \DomainException
     * @codeCoverageIgnore
     */
    private function openSocketClient()
    {
        $streamContext = stream_context_create(
            [
                'ssl' => [
                    'local_cert'        => $this->pemCertificatePath,
                    'passphrase'        => $this->pemCertificatePassword,
                    'allow_self_signed' => true,
                    'verify_peer'       => false,
                ],
            ]
        );

        $errorCode    = 0;
        $errorMessage = '';

        $socketClient = stream_socket_client(
            $this->apnServerAddress,
            $errorCode,
            $errorMessage,
            $this->timeout,
            STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT,
            $streamContext
        );

        if (is_resource($socketClient)) {
            stream_set_blocking($socketClient, false);
        } else {
            throw new \DomainException(
                sprintf(
                    'Cant\'t create socket client. Error %s: %s.',
                    $errorCode,
                    $errorMessage
                )
            );
        }

        return $socketClient;
    }
}
