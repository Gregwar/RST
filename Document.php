<?php

namespace Gregwar\RST;

use Gregwar\RST\Nodes\Node;

class Document extends Node
{
    protected $headerNodes = array();
    protected $nodes = array();

    public function render()
    {
        $document = '';
        foreach ($this->nodes as $node) {
            $document .= $node->render()."\n\n";
        }

        return $document;
    }

    public function renderHTML()
    {
        $document = "<!DOCTYPE html>\n";
        $document .= "<html>\n";

        $document .= "<head>\n";
        $document .= "<meta charset=\"utf-8\" />\n";
        foreach ($this->headerNodes as $node) {
            $document .= $node->render()."\n";
        }
        $document .= "</head>\n";

        $document .= "<body>\n";
        $document .= $this->render();
        $document .= "</body>\n";
        $document .= "</html\n";

        return $document;
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
