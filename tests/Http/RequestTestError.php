<?php

namespace Nazz\WebPush\Http;

function function_exists($function)
{
    if ($function === 'curl_init') {
        return false;
    }

    return \function_exists($function);
}

/**
 * Class RequestTest
 */
class RequestTestError extends \PHPUnit_Framework_TestCase
{
    public function testRequest()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Curl is required for HTTP protocol!');
        new Request();
    }
}
