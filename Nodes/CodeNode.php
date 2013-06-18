<?php

namespace Gregwar\RST\Nodes;

class CodeNode extends Node
{
    public function render()
    {
        return "<pre><code>".htmlspecialchars($this->value)."</code></pre>";
    }
}
