<?php

namespace Gregwar\RST\LaTeX\Directives;

use Gregwar\RST\LaTeX\Nodes\LaTeXMainNode;
use Gregwar\RST\Parser;
use Gregwar\RST\Directive;

/**
 * Marks the document as LaTeX main
 */
class LaTeXMain extends Directive
{
    public function getName()
    {
        return 'latex-main';
    }

    public function processNode(Parser $parser, $variable, $data, array $options)
    {
        return new LaTeXMainNode;
    }
}
