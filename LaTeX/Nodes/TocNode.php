<?php

namespace Gregwar\RST\LaTeX\Nodes;

use Gregwar\RST\Nodes\TocNode as Base;

class TocNode extends Base
{
    public function render()
    {
        $tex = '\tableofcontents'."\n";

        foreach ($this->files as $file) {
            $reference = $this->environment->resolve('doc', $file);
            $reference['url'] = $this->environment->relativeUrl($reference['url']);
            $tex .= "\\input{".$reference['url']."}\n";
        }

        return $tex;
    }
}
