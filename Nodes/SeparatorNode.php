<?php

namespace Gregwar\RST\Nodes;

class SeparatorNode extends Node
{
    public function render()
    {
        return '<hr />';
    }
}
