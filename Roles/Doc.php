<?php

namespace Gregwar\RST\Roles;

class Doc implements Role
{
    /**
     * @var string
     */
    public $url;

    /**
     * @var string|null
     */
    public $text;

    /**
     * @var string|null
     */
    public $anchor;

    /**
     * @var DocReference|null
     */
    public $reference;

    /**
     * @param string $url
     * @param string|null $text
     * @param string|null $anchor
     */
    public function __construct($url, $text, $anchor)
    {
        $this->url = $url;
        $this->text = $text;
        $this->anchor = $anchor;
    }
}
