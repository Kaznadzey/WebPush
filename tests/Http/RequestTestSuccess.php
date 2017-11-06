<?php

namespace Nazz\WebPush\Http;

function function_exists($function)
{
    if ($function === 'curl_init') {
        return true;
    }

    return \function_exists($function);
}

/**
 * Class RequestTest
 */
class RequestTestSuccess extends \PHPUnit_Framework_TestCase
{
    public function testRequest()
    {
        $request = new Request();

        $this->assertInstanceOf(Request::class, $request);
    }
}
