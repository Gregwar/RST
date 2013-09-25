<?php

namespace Gregwar\RST\LaTeX\Nodes;

use Gregwar\RST\Nodes\TitleNode as Base;

class TitleNode extends Base
{
    public function render()
    {
        $type = 'chapter';

        if ($this->level > 1) {
            $type = 'section';

            for ($i=2; $i<$this->level; $i++) {
                $type = 'sub'.$type;
            }
        }

        return '\\'.$type.'{'.$this->value.'}';
    }
}
