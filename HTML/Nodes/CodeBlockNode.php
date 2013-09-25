<?php

namespace Gregwar\RST\HTML\Nodes;

use Gregwar\RST\Nodes\CodeBlockNode as Base;

class CodeBlockNode extends Base
{
    public function render()
    {
        return '<div class="codeBlock">'.$this->value.'</div>';
    }
}
