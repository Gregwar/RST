<?php

namespace Gregwar\RST\Directives;

use Gregwar\RST\Nodes\TitleNode;
use Gregwar\RST\Span;
use Gregwar\RST\Parser;
use Gregwar\RST\Directive;

/**
 * This sets a new target for a following title, this can be used to change
 * its link
 */
class RedirectionTitle extends Directive
{
    public function getName()
    {
        return 'redirection-title';
    }

    public function process(Parser $parser, $node, $variable, $data, array $options)
    {
        $document = $parser->getDocument();

        if ($node) {
            if ($node instanceof TitleNode) {
                $node->setTarget($data);
            }
            $document->addNode($node);
        }
    }
}
