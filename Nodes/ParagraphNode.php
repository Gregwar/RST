<?php

namespace Gregwar\RST\Nodes;

class ParagraphNode extends Node
{
    public function render()
    {
        return "<p>".$this->value."</p>";
    }
}
