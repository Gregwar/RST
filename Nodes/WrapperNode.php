<?php

namespace Gregwar\RST\Nodes;

class WrapperNode extends Node
{
    protected $node;
    protected $before;
    protected $after;

    public function __construct($node, $before = '', $after = '')
    {
        $this->node = $node;
        $this->before = $before;
        $this->after = $after;
    }

    public function render()
    {
        $contents = $this->node ? $this->node->render() : '';

        return $this->before . $contents . $this->after;
    }
}
