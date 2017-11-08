<?php
require 'loader.php';

use Nazz\WebPush\Subscription\APN\PackageGeneratorDto;
use Nazz\WebPush\Subscription\APN\PackageGenerator;

$dto = new PackageGeneratorDto(
    'TestSite',
    'my.app.id',
    'https://test.site',
    '/open/%s',
    'certificate/p12/filepath.p12',
    'certificate/pem/filepath.pem',
    'my-password',
    'iconset/'
);

$packageGenerator = new PackageGenerator();

$pushPackage = $packageGenerator->createPackage($dto);

var_dump($packageGenerator->getPushPackageContent($pushPackage));
