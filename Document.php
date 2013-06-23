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
