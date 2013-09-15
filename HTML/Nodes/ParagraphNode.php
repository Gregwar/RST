<?php

namespace Gregwar\RST\HTML\Nodes;

use Gregwar\RST\Nodes\ParagraphNode as Base;

class ParagraphNode extends Base
{
    public function render()
    {
        $text = $this->value;

        if (trim($text)) {
            return '<p>'.$text.'</p>';
        } else {
            return '';
        }
    }
}
