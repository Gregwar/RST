<?php

namespace Gregwar\RST\Nodes;

class WrapperNode extends Node
{
    protected $node;
    protected $before;
    protected $after;

    public function __construct(Node $node, $before = '', $after = '')
    {
        $this->node = $node;
        $this->before = $before;
        $this->after = $after;
    }

    public function render()
    {
        return $this->before . $this->node->render() . $this->after;
    }
}
