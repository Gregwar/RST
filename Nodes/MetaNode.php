<?php

namespace Gregwar\RST\Nodes;

abstract class MetaNode extends Node
{
    protected $key;
    protected $value;

    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }
}
