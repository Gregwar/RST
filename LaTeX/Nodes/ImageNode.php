<?php

namespace Gregwar\RST\LaTeX\Nodes;

use Gregwar\RST\Nodes\ImageNode as Base;

class ImageNode extends Base
{
    public function render()
    {
        $attributes = array();
        foreach ($this->options as $key => $value) {
            $attributes[] = $key . '='.$value;
        }

        return '\includegraphics{'.$this->url.'}';
    }
}
