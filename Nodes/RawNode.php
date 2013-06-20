<?php

namespace Gregwar\RST\Nodes;

class RawNode extends Node
{
    public function render()
    {
        return $this->value;
    }
}
