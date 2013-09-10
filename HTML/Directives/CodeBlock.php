<?php

namespace Gregwar\RST\HTML\Directives;

use Gregwar\RST\Parser;
use Gregwar\RST\Directive;

use Gregwar\RST\Nodes\WrapperNode;
use Gregwar\RST\Nodes\CodeNode;

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
            if ($node instanceof CodeNode) {
                $node->setLanguage(trim($data));
            }

            $document = $parser->getDocument();
            $document->addNode(new WrapperNode($node, '<div class="codeBlock">', '</div>'));
        }
    }

    public function wantCode()
    {
        return true;
    }
}
