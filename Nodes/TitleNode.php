<?php

namespace Gregwar\RST\Nodes;

abstract class TitleNode extends Node
{
    protected $level;
    protected $token;

    public function __construct($value, $level, $token)
    {
        parent::__construct($value);
        $this->level = $level;
        $this->token = $token;
    }

    public function getLevel()
    {
        return $this->level;
    }
}
