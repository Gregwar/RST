<?php

namespace Gregwar\RST\Nodes;

class ImageNode extends Node
{
    protected $url;
    protected $options;

    public function __construct($url, array $options = array())
    {
        $this->url = $url;
        $this->options = $options;
    }

    public function render()
    {
        $attributes = '';
        foreach ($this->options as $key => $value) {
            $attributes .= ' '.$key . '="'.htmlspecialchars($value).'"';
        }

        return '<img src="'.$this->url.'" '.$attributes.' />';
    }
}
