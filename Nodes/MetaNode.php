<?php

namespace Gregwar\RST\Nodes;

class MetaNode extends Node
{
    protected $key;
    protected $value;

    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    public function render()
    {
        return '<meta name="'.htmlspecialchars($this->key).'" content="'.htmlspecialchars($this->value).'" />';
    }
}
