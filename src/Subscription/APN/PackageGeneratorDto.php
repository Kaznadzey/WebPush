<?php

namespace Nazz\WebPush\Subscription\APN;

/**
 * Class PackageGeneratorDto
 */
class PackageGeneratorDto
{
    /** @var string */
    private $webSiteName;

    /** @var string */
    private $appId;

    /** @var string */
    private $webServiceUrl;

    /** @var string */
    private $urlFormatString;

    /** @var string */
    private $authenticationToken;

    /** @var string */
    private $certificateP12FilePath;

    /** @var string */
    private $certificatePEMFilePath;

    /** @var string */
    private $certificateP12Password;

    /** @var string */
    private $iconSetDirPath;

    /**
     * @param string $webSiteName
     * @param string $appId
     * @param string $webServiceUrl
     * @param string $urlFormatString
     * @param string $certificateP12FilePath
     * @param string $certificatePEMFilePath
     * @param string $certificateP12Password
     * @param string $iconSetDirPath
     */
    public function __construct(
        $webSiteName,
        $appId,
        $webServiceUrl,
        $urlFormatString,
        $certificateP12FilePath,
        $certificatePEMFilePath,
        $certificateP12Password,
        $iconSetDirPath
    ) {
        $this->webSiteName            = $webSiteName;
        $this->appId                  = $appId;
        $this->webServiceUrl          = $webServiceUrl;
        $this->urlFormatString        = $urlFormatString;
        $this->certificateP12FilePath = $certificateP12FilePath;
        $this->certificatePEMFilePath = $certificatePEMFilePath;
        $this->certificateP12Password = $certificateP12Password;
        $this->iconSetDirPath         = $iconSetDirPath;
    }

    /**
     * @return string
     */
    public function getWebSiteName()
    {
        return $this->webSiteName;
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @return string
     */
    public function getWebServiceUrl()
    {
        return $this->webServiceUrl;
    }

    /**
     * @return string
     */
    public function getUrlFormatString()
    {
        return $this->urlFormatString;
    }

    /**
     * @return string
     */
    public function getAuthenticationToken()
    {
        return $this->authenticationToken;
    }

    /**
     * @param string $authenticationToken
     */
    public function setAuthenticationToken($authenticationToken)
    {
        $this->authenticationToken = $authenticationToken;
    }

    /**
     * @return string
     */
    public function getCertificateP12FilePath()
    {
        return $this->certificateP12FilePath;
    }

    /**
     * @return string
     */
    public function getCertificatePEMFilePath()
    {
        return $this->certificatePEMFilePath;
    }

    /**
     * @return string
     */
    public function getCertificateP12Password()
    {
        return $this->certificateP12Password;
    }

    /**
     * @return string
     */
    public function getIconSetDirPath()
    {
        return $this->iconSetDirPath;
    }
}
