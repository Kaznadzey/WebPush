<?php

namespace Nazz\WebPush\Sender;

/**
 * Class Message
 */
class Message
{
    /** @var string */
    protected $body;

    /** @var string */
    protected $icon;

    /** @var string */
    protected $id;

    /** @var string */
    protected $title;

    /** @var string */
    protected $url;

    /** @var int */
    protected $ttl;

    /**
     * @param string $id
     * @param string $title
     * @param string $body
     * @param string $icon
     * @param string $url
     */
    public function __construct($id, $title, $body, $icon, $url, $ttl = 0)
    {
        $this->id    = $id;
        $this->title = $title;
        $this->body  = $body;
        $this->icon  = $icon;
        $this->url   = $url;
        $this->ttl = $ttl;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }
}
