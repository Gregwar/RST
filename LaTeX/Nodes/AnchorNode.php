<?php

namespace Gregwar\RST\LaTeX\Nodes;

use Gregwar\RST\Nodes\AnchorNode as Base;

class AnchorNode extends Base
{
    public function render()
    {
        return '\label{'.$this->value.'}';
    }
}
