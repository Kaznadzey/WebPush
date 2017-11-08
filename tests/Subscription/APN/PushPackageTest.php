<?php

namespace Nazz\WebPush\Subscription\APN;

/**
 * Class PushPackageTest
 */
class PushPackageTest extends \PHPUnit_Framework_TestCase
{
    public function testPushPackage()
    {
        $name = 'name';
        $path = 'path';

        $package = new PushPackage($name, $path);

        $this->assertEquals($package->getName(), $name);
        $this->assertEquals($package->getPath(), $path);
    }
}
