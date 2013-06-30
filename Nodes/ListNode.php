<?php

namespace Gregwar\RST\Nodes;

abstract class ListNode extends Node
{
    abstract public function addLine($text, $ordered, $depth);
}
