<?php

namespace Gregwar\RST\HTML\Nodes;

use Gregwar\RST\Nodes\QuoteNode as Base;

class QuoteNode extends Base
{
    public function render()
    {
        return "<blockquote>".$this->value."</blockquote>";
    }
}
