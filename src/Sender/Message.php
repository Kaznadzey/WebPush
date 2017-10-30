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

    /**
     * @param string $id
     * @param string $title
     * @param string $body
     * @param string $icon
     * @param string $url
     */
    public function __construct($id, $title, $body, $icon, $url)
    {
        $this->id    = $id;
        $this->title = $title;
        $this->body  = $body;
        $this->icon  = $icon;
        $this->url   = $url;
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
}