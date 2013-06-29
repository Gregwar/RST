<?php

namespace Gregwar\RST\Nodes;

abstract class Node
{
    protected $value;

    public function __construct($value = null)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    abstract public function render();
}
