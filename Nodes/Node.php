<?php

namespace Gregwar\RST\Nodes;

class Node
{
    protected $value;

    public function __construct($value = null)
    {
        $this->value = $value;
    }

    public function render()
    {
        return "<p>".$this->value."</p>";
    }
}
