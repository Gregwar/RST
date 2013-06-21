<?php

namespace Gregwar\RST;

use Gregwar\RST\Nodes\Node;

class Document extends Node
{
    protected $nodes = array();

    public function render()
    {
        $document = '';

        foreach ($this->nodes as $node) {
            $document .= $node->render()."\n\n";
        }

        return $document;
    }

    public function addNode(Node $node)
    {
        $this->nodes[] = $node;
    }

    public function __toString()
    {
        return $this->render();
    }
}
