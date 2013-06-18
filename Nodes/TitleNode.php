<?php

namespace Gregwar\RST\Nodes;

class TitleNode extends Node
{
    protected $level;

    public function __construct($value, $level)
    {
        parent::__construct($value);
        $this->level = $level;
    }

    public function render()
    {
        return '<h'.$this->level.'>'.$this->value.'</h'.$this->level.">";
    }
}
