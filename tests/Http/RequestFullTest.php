<?php

namespace Nazz\WebPush\Http;

define('CURLOPT_URL', 1);
define('CURLOPT_POST', 2);
define('CURLOPT_HTTPHEADER', 3);
define('CURLOPT_RETURNTRANSFER', 4);
define('CURLOPT_POSTFIELDS', 5);

function function_exists($function)
{
    if ($function === 'curl_init') {
        return true;
    }

    return \function_exists($function);
}

function curl_init()
{
    return true;
}

function curl_setopt($optName, $optValue)
{
    return true;
}

function curl_close()
{
    return true;
}

function curl_exec()
{
    return true;
}

/**
 * Class RequestFullTest
 */
class RequestFullTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    public function testRequest()
    {
        $request = new Request();

        $this->assertTrue($request->sendPost('', array(), ''));
    }
}
