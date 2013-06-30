<?php

namespace Gregwar\RST\HTML;

use Gregwar\RST\Nodes\ParagraphNode as Base;

class ParagraphNode extends Base
{
    public function render()
    {
        return "<p>".$this->value."</p>";
    }
}
