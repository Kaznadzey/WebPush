<?php

namespace Nazz\WebPush\Subscription\APN;

/**
 * Class PushPackage
 */
class PushPackage
{
    /** @var string */
    private $name;

    /** @var string */
    private $path;

    /**
     * @param string $name
     * @param string $path
     */
    public function __construct($name, $path)
    {
        $this->name = $name;
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
