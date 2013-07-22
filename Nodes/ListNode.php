<?php

namespace Gregwar\RST\Nodes;

abstract class ListNode extends Node
{
    /**
     * Infos contains:
     * 
     * - text: the line text
     * - depth: the depth in the list level
     * - prefix: the prefix char (*, - etc.)
     * - ordered: true of false if the list is ordered
     */
    abstract public function addLine(array $infos);
}
