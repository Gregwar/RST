<?php

namespace Gregwar\RST\Nodes;

use Gregwar\RST\Nodes\Node;

class CodeBlockNode extends Node
{
    public function render()
    {
        return $this->value;
    }
}
