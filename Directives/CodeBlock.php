<?php

namespace Gregwar\RST\Directives;

use Gregwar\RST\Parser;
use Gregwar\RST\Directive;

use Gregwar\RST\Nodes\WrapperNode;

/**
 * Renders a code block
 *
 * .. code-block:: php
 *
 *      <?php
 *
 *      echo "Hello world!\n";
 */
class CodeBlock extends Directive
{
    public function getName()
    {
        return 'code-block';
    }

    public function process(Parser $parser, $node, $variable, $data, array $options)
    {
        if ($node) {
            $document = $parser->getDocument();
            $document->addNode(new WrapperNode($node, '<div class="codeBlock">', '</div>'));
        }
    }
}
