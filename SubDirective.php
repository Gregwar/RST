<?php

namespace Gregwar\RST;

use Gregwar\RST\Nodes\CodeNode;

/**
 * A directive that parses the sub block and call the processSub that can 
 * be overloaded, like :
 *
 * .. sub-directive::
 *      Some block of code
 *
 *      You can imagine anything here, like adding *emphasis*, lists or
 *      titles
 */
abstract class SubDirective extends Directive
{
    public function process(Parser $parser, $node, $variable, $data, array $options)
    {
        $subParser = $parser->getSubParser();

        if ($node instanceof CodeNode) {
            $data = $node->getValue();
            $document = $subParser->parse($data);
        } else {
            $document = $node;
        }

        $newNode = $this->processSub($parser, $document, $variable, $data, $options);

        if ($newNode) {
            $parser->getDocument()->addNode($newNode);
        }
    }

    public function processSub(Parser $parser, $document, $variable, $data, array $options)
    {
        return $document;
    }

    public function wantCode()
    {
        return true;
    }
}
