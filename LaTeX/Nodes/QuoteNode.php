<?php

namespace Gregwar\RST\LaTeX\Nodes;

use Gregwar\RST\Nodes\QuoteNode as Base;

class QuoteNode extends Base
{
    public function render()
    {
        return "\\begin{quotation}\n".$this->value."\n\\end{quotation}\n";
    }
}
