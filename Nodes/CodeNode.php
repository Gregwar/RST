<?php

namespace Gregwar\RST\Nodes;

class CodeNode extends BlockNode
{
    public function render()
    {
        return "<pre><code>".htmlspecialchars($this->value)."</code></pre>";
    }
}
