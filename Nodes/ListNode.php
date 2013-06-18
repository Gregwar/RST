<?php

namespace Gregwar\RST\Nodes;

class ListNode extends Node
{
    protected $stack = array();
    protected $currentDepth = 0;

    public function addLine($text, $ordered, $depth)
    {
        $keyword = $ordered ? 'ol' : 'ul';
        $depth += 1;

        if ($this->currentDepth < $depth) {
            $this->currentDepth = $depth;
            $this->value .= '<'.$keyword.'>'."\n";
            $this->stack[] = array($depth, '</' . $keyword . '>'."\n");
        }
        $this->popTo($depth);

        $this->value .= '<li>'.$text.'</li>'."\n";
    }

    protected function popTo($depth)
    {
        while ($this->currentDepth > $depth) {
            if ($this->stack) {
                $value = array_pop($this->stack);
                $this->value .= $value[1];

                if ($this->stack) {
                    $this->currentDepth = $this->stack[count($this->stack)-1][0];
                } else {
                    $this->currentDepth = 0;
                }
            } else {
                break;
            }
        }
    }

    public function close()
    {
        $this->popTo(0);
    }

    public function render()
    {
        return '<p>'.$this->value.'</p>';
    }
}
