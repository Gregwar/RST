<?php

namespace Gregwar\RST\HTML\Nodes;

use Gregwar\RST\Nodes\CodeNode as Base;

class CodeNode extends Base
{
    public function render()
    {
        if ($this->raw) {
            return $this->value;
        } else {
            return "<pre><code class=\"".$this->language."\">".htmlspecialchars($this->value)."</code></pre>";
        }
    }
}
