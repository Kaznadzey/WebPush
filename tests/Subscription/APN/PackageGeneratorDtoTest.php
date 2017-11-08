<?php

namespace Nazz\WebPush\Subscription\APN;

/**
 * Class PackageGeneratorDtoTest
 */
class PackageGeneratorDtoTest extends \PHPUnit_Framework_TestCase
{
    public function testPushPackageGeneratorDto()
    {
        $siteName            = 'TestSite';
        $appId               = 'my.app.id';
        $webSiteUrl          = 'https://test.site';
        $relativePath        = '/open/%s';
        $p12CErtificatePath  = 'certificate/p12/filepath.p12';
        $pemCertificatePath  = 'certificate/pem/filepath.pem';
        $certificatePassword = 'my-password';
        $iconsPath           = 'iconset/';

        $dto = new PackageGeneratorDto(
            $siteName,
            $appId,
            $webSiteUrl,
            $relativePath,
            $p12CErtificatePath,
            $pemCertificatePath,
            $certificatePassword, $iconsPath
        );

        $this->assertEquals($siteName, $dto->getWebSiteName());
        $this->assertEquals($appId, $dto->getAppId());
        $this->assertEquals($webSiteUrl, $dto->getWebServiceUrl());
        $this->assertEquals($relativePath, $dto->getUrlFormatString());
        $this->assertEquals($p12CErtificatePath, $dto->getCertificateP12FilePath());
        $this->assertEquals($pemCertificatePath, $dto->getCertificatePEMFilePath());
        $this->assertEquals($certificatePassword, $dto->getCertificateP12Password());
        $this->assertEquals($iconsPath, $dto->getIconSetDirPath());
    }
}
