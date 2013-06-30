<?php

namespace Gregwar\RST;

use Gregwar\RST\Nodes\Node;

abstract class Document extends Node
{
    protected $headerNodes = array();
    protected $nodes = array();

    public function renderDocument()
    {
        return $this->render();
    }

    /**
     * Getting all nodes of the document that satisfies the given
     * function. If the function is null, all the nodes are returned.
     */
    public function getNodes($function = null)
    {
        $nodes = array();

        if ($function == null) {
            return $this->nodes;
        }

        foreach ($this->nodes as $node) {
            if ($function($node)) {
                $nodes[] = $node;
            }
        }

        return $nodes;
    }

    public function addNode(Node $node)
    {
        $this->nodes[] = $node;
    }

    public function addHeaderNode(Node $node)
    {
        $this->headerNodes[] = $node;
    }

    public function __toString()
    {
        return $this->render();
    }
}
