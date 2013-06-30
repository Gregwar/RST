<?php

namespace Gregwar\RST\HTML;

use Gregwar\RST\Nodes\QuoteNode as Base;

class QuoteNode extends Base
{
    public function render()
    {
        return "<blockquote>".nl2br(htmlspecialchars($this->value))."</blockquote>";
    }
}
