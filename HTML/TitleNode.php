<?php

namespace Gregwar\RST\HTML;

use Gregwar\RST\Nodes\TitleNode as Base;

class TitleNode extends Base
{
    public function render()
    {
        return '<h'.$this->level.'>'.$this->value.'</h'.$this->level.">";
    }
}
