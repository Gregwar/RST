<?php

namespace Gregwar\RST;

use Gregwar\RST\Nodes\Node;
use Gregwar\RST\Nodes\TitleNode;

abstract class Document extends Node
{
    protected $environment;
    protected $headerNodes = array();
    protected $nodes = array();

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

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

    /**
     * Gets the main title of the document
     */
    public function getTitle()
    {
        foreach ($this->nodes as $node) {
            if ($node instanceof TitleNode && $node->getLevel() == 1) {
                return $node->getValue().'';
            }
        }

        return null;
    }

    /**
     * Gets the titles hierarchy in arrays, for instance :
     *
     * array(
     *     array('Main title', array(
     *         array('Sub title', array()),
     *         array('Sub title 2', array()
     *     )
     * )
     */
    public function getTitles()
    {
        $titles = array();
        $levels = array(&$titles);

        foreach ($this->nodes as $node) {
            if ($node instanceof TitleNode) {
                $level = $node->getLevel();
                $text = $node->getValue() . '';

                if (isset($levels[$level-1])) {
                    $parent = &$levels[$level-1];
                    $element = array($text, array());
                    $parent[] = $element;
                    $levels[$level] = &$parent[count($parent)-1][1];
                }
            }
        }

        return $titles;
    }

    public function addNode(Node $node)
    {
        $this->nodes[] = $node;
    }

    public function prependNode(Node $node)
    {
        array_unshift($this->nodes, $node);
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
