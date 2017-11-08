<?php

namespace Nazz\WebPush\Subscription\APN;

/**
 * Class PackageGenerator
 */
class PackageGenerator
{
    const ICONSET_DIR_NAME        = 'icon.iconset';
    const WEBSITE_JSON_FILENAME   = 'website.json';
    const MANIFEST_JSON_FILE_NAME = 'manifest.json';
    const SIGNATURE_FILE_NAME     = 'signature';
    const ARCHIVE_NAME_PATTERN    = 'pushPackage%s.zip';

    /**
     * @param PackageGeneratorDto $packageGeneratorDto
     *
     * @return PushPackage
     * @throws \DomainException
     */
    public function createPackage(PackageGeneratorDto $packageGeneratorDto)
    {
        $pushPackage = $this->createPushPackage($packageGeneratorDto);

        $package = new \ZipArchive();
        $package->open($pushPackage->getPath(), \ZipArchive::CREATE);

        $package->addEmptyDir(self::ICONSET_DIR_NAME);
        foreach ($this->getIcons($packageGeneratorDto) as $iconName => $iconPath) {
            $package->addFile($iconPath, self::ICONSET_DIR_NAME . DIRECTORY_SEPARATOR . $iconName);
        }

        $webSiteJson  = $this->createWebSiteJson($packageGeneratorDto, $this->getJsonFileContent());
        $manifestJson = $this->createMainFestJson($packageGeneratorDto, $webSiteJson);
        $signature    = $this->createSignature($packageGeneratorDto, $manifestJson);

        $package->addFromString(self::WEBSITE_JSON_FILENAME, $webSiteJson);
        $package->addFromString(self::MANIFEST_JSON_FILE_NAME, $manifestJson);
        $package->addFromString(self::SIGNATURE_FILE_NAME, $signature);

        $package->close();

        return $pushPackage;
    }

    /**
     * @param PushPackage $pushPackage
     *
     * @return string
     * @throws \DomainException
     */
    public function getPushPackageContent(PushPackage $pushPackage)
    {
        if (is_readable($pushPackage->getPath())) {
            $packageContent = file_get_contents($pushPackage->getPath());
        } else {
            unlink($pushPackage->getPath());

            throw new \DomainException('Push package file is not readable!');
        }

        unlink($pushPackage->getPath());

        return $packageContent;
    }

    /**
     * @return string
     * @throws \DomainException
     */
    protected function getJsonFileContent()
    {
        $content = $this->getFileContent(dirname(__FILE__) . DIRECTORY_SEPARATOR . self::WEBSITE_JSON_FILENAME);

        return $content;
    }

    /**
     * @param string $path
     *
     * @return string
     * @throws \DomainException
     */
    protected function getFileContent($path)
    {
        if (!is_file($path)) {
            throw new \DomainException(
                sprintf(
                    'File not exists: %s',
                    $path
                )
            );
        }

        $content = file_get_contents($path);

        if (!is_string($content)) {
            throw new \DomainException(
                sprintf(
                    'Can\'t read file: %s',
                    dirname(__FILE__) . DIRECTORY_SEPARATOR . self::WEBSITE_JSON_FILENAME
                )
            );
        }

        return $content;
    }

    /**
     * @param PackageGeneratorDto $packageGeneratorDto
     * @param string              $webSiteJson
     *
     * @return string
     * @throws \DomainException
     */
    protected function createMainFestJson(PackageGeneratorDto $packageGeneratorDto, $webSiteJson)
    {
        $manifestData = [];
        foreach ($this->getIcons($packageGeneratorDto) as $iconName => $iconPath) {
            $manifestData[self::ICONSET_DIR_NAME . DIRECTORY_SEPARATOR . $iconName] = sha1(
                $this->getFileContent($iconPath)
            );
        }

        $manifestData[self::WEBSITE_JSON_FILENAME] = sha1($webSiteJson);

        return json_encode((object) $manifestData);
    }

    /**
     * @param PackageGeneratorDto $packageGeneratorDto
     *
     * @return array
     */
    private function getIcons(PackageGeneratorDto $packageGeneratorDto)
    {
        $iconNames = [
            'icon_16x16.png',
            'icon_16x16@2x.png',
            'icon_32x32.png',
            'icon_32x32@2x.png',
            'icon_128x128.png',
            'icon_128x128@2x.png',
        ];

        $icons = [];

        foreach ($iconNames as $name) {
            $icons[$name] = $packageGeneratorDto->getIconSetDirPath() . $name;
        }

        return $icons;
    }

    /**
     * @param PackageGeneratorDto $packageGeneratorDto
     * @param string              $jsonContent
     *
     * @return string
     */
    private function createWebSiteJson(PackageGeneratorDto $packageGeneratorDto, $jsonContent)
    {
        $replaceFrom = [
            '{{ webSiteName }}',
            '{{ appId }}',
            '{{ webServiceUrl }}',
            '{{ urlFormatString }}',
            '{{ authenticationToken }}',
            "\n",
        ];

        $replaceTo = [
            $packageGeneratorDto->getWebSiteName(),
            $packageGeneratorDto->getAppId(),
            $packageGeneratorDto->getWebServiceUrl(),
            $packageGeneratorDto->getUrlFormatString(),
            $packageGeneratorDto->getAuthenticationToken(),
            '',
        ];

        $preparedJsonContent = str_replace($replaceFrom, $replaceTo, $jsonContent);
        $preparedJsonContent = preg_replace('/\s/', '', $preparedJsonContent);

        return $preparedJsonContent;
    }

    /**
     * @param PackageGeneratorDto $packageGeneratorDto
     * @param string              $manifestJson
     *
     * @return string
     * @throws \DomainException
     */
    private function createSignature(PackageGeneratorDto $packageGeneratorDto, $manifestJson)
    {
        $signature   = '';
        $pkcs12      = $this->getFileContent($packageGeneratorDto->getCertificateP12FilePath());
        $certificate = [];

        if (openssl_pkcs12_read($pkcs12, $certificate, $packageGeneratorDto->getCertificateP12Password())) {
            $certificateData       = openssl_x509_read(isset($certificate['cert']) ? $certificate['cert'] : '');
            $certificatePrivateKey = openssl_pkey_get_private(
                isset($certificate['pkey']) ? $certificate['pkey'] : '',
                $packageGeneratorDto->getCertificateP12Password()
            );
            $manifestFile          = $this->getTemporaryDirPath() . self::MANIFEST_JSON_FILE_NAME;
            $signatureFile         = $this->getTemporaryDirPath() . self::SIGNATURE_FILE_NAME;

            file_put_contents($manifestFile, $manifestJson);

            openssl_pkcs7_sign(
                $manifestFile,
                $signatureFile,
                $certificateData,
                $certificatePrivateKey,
                [],
                PKCS7_BINARY | PKCS7_DETACHED,
                $packageGeneratorDto->getCertificatePEMFilePath()
            );

            $signaturePem = $this->getFileContent($signatureFile);
            $matches      = [];

            if (preg_match(
                '~Content-Disposition:[^\n]+\s*?([A-Za-z0-9+=/\r\n]+)\s*?-----~',
                $signaturePem,
                $matches
            )) {
                $signature = base64_decode(isset($matches[1]) ? $matches[1] : '');
            }

            unlink($signatureFile);
            unlink($manifestFile);
        }

        return $signature;
    }

    /**
     * @param PackageGeneratorDto $packageGeneratorDto
     *
     * @return PushPackage
     */
    private function createPushPackage(PackageGeneratorDto $packageGeneratorDto)
    {
        $archiveName = sprintf(self::ARCHIVE_NAME_PATTERN, $packageGeneratorDto->getAuthenticationToken());

        $pushPackage = new PushPackage($archiveName, $this->getTemporaryDirPath() . $archiveName);

        return $pushPackage;
    }

    /**
     * @return string
     */
    private function getTemporaryDirPath()
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR;
    }
}
