<?php

namespace Gregwar\RST\HTML;

use Gregwar\RST\Nodes\CodeNode as Base;

class CodeNode extends Base
{
    public function render()
    {
        return "<pre><code>".htmlspecialchars($this->value)."</code></pre>";
    }
}
