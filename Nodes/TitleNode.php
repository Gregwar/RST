<?php

namespace Gregwar\RST\Nodes;

abstract class TitleNode extends Node
{
    protected $level;

    public function __construct($value, $level)
    {
        parent::__construct($value);
        $this->level = $level;
    }

    public function getLevel()
    {
        return $this->level;
    }
}
