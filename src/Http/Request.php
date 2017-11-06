<?php

namespace Nazz\WebPush\Http;

/**
 * Class Request
 */
class Request
{
    /**
     * Request constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        if (!function_exists('curl_init')) {
            throw new \Exception(
                'Curl is required for HTTP protocol!'
            );
        }
    }

    /**
     * @param string $urlTo
     * @param array  $headers
     * @param string $encodedParams
     *
     * @return mixed
     */
    public function sendPost($urlTo, array $headers, $encodedParams)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlTo);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedParams);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}
