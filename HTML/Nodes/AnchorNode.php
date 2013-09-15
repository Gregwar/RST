<?php

namespace Gregwar\RST\HTML\Nodes;

use Gregwar\RST\Nodes\AnchorNode as Base;

class AnchorNode extends Base
{
    public function render()
    {
        return '<a id="'.$this->value.'"></a>';
    }
}
