<?php

namespace Gregwar\RST\HTML;

use Gregwar\RST\Nodes\SeparatorNode as Base;

class SeparatorNode extends Base
{
    public function render()
    {
        return '<hr />';
    }
}
