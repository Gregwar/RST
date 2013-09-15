<?php

namespace Gregwar\RST\HTML\Nodes;

use Gregwar\RST\Nodes\MetaNode as Base;

class MetaNode extends Base
{
    public function render()
    {
        return '<meta name="'.htmlspecialchars($this->key).'" content="'.htmlspecialchars($this->value).'" />';
    }
}
