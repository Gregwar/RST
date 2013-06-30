<?php

namespace Gregwar\RST\Nodes;

abstract class ImageNode extends Node
{
    protected $url;
    protected $options;

    public function __construct($url, array $options = array())
    {
        $this->url = $url;
        $this->options = $options;
    }
}
