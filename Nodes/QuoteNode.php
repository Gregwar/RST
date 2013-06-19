<?php

namespace Gregwar\RST\Nodes;

class QuoteNode extends Node
{
    public function render()
    {
        return "<blockquote>".nl2br(htmlspecialchars($this->value))."</blockquote>";
    }
}
