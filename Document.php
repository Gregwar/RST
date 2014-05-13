<?php

namespace Gregwar\RST;

use Gregwar\RST\Nodes\Node;
use Gregwar\RST\Nodes\TitleNode;
use Gregwar\RST\Nodes\TocNode;
use Gregwar\RST\Nodes\RawNode;

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
     * Get the table of contents of the document
     */
    public function getTocs()
    {
        $tocs = array();

        $nodes = $this->getNodes(function($node) {
            return $node instanceof TocNode;
        });

        foreach ($nodes as $toc) {
            $files = $toc->getFiles();

            foreach ($files as &$file) {
                $file = $this->environment->canonicalUrl($file);
            }

            $tocs[] = $files;
        }

        return $tocs;
    }

    /**
     * Gets the titles hierarchy in arrays, for instance :
     *
     * array(
     *     array('Main title', array(
     *         array('Sub title', array()),
     *         array('Sub title 2', array(),
     *         array(array('Redirection', 'target'), array(),
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
                $text = (string)$node->getValue();
                $redirection = $node->getTarget();
                $value = $redirection ? array($text, $redirection) : $text;

                if (isset($levels[$level-1])) {
                    $parent = &$levels[$level-1];
                    $element = array($value, array());
                    $parent[] = $element;
                    $levels[$level] = &$parent[count($parent)-1][1];
                }
            }
        }

        return $titles;
    }

    public function addNode($node)
    {
        if (is_string($node)) {
            $node = new RawNode($node);
        }

        if (!$node instanceof Node) {
            $this->getEnvironment()->getErrorManager('addNode($node): $node should be a string or a Node');
        }

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
