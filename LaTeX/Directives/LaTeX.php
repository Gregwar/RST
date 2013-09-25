<?php

namespace Gregwar\RST\LaTeX\Directives;

use Gregwar\RST\Parser;
use Gregwar\RST\Directive;

use Gregwar\RST\LaTeX\Nodes\LaTeXNode;

/**
 * Tell that this document should be latex compilable
 */
class LaTeX extends Directive
{
    public function getName()
    {
        return 'latex';
    }

    public function processNode(Parser $parser, $variable, $data, array $options)
    {
        return new LaTeXNode;
    }
}
