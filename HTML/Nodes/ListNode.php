<?php

namespace Gregwar\RST\HTML\Nodes;

use Gregwar\RST\Nodes\ListNode as Base;

class ListNode extends Base
{
    protected $lines = array();

    public function addLine(array $line)
    {
        $this->lines[] = $line;
    }

    public function createElement($text, $prefix)
    {
        $class = '';
        if ($prefix == '-') {
            $class = ' class="dash"';
        }

        return '<li' . $class . '>' . $text . '</li>';
    }

    public function render()
    {
        $depth = -1;
        $value = '';
        $stack = array();

        foreach ($this->lines as $line) {
            $prefix = $line['prefix'];
            $text = $line['text'];
            $ordered = $line['ordered'];
            $newDepth = $line['depth'];
            $keyword = $ordered ? 'ol' : 'ul';

            if ($depth < $newDepth) {
                $value .= '<' . $keyword . '>'."\n";
                $stack[] = array($newDepth, '</' . $keyword . '>'."\n");
                $depth = $newDepth;
            }

            while ($depth > $newDepth) {
                $top = $stack[count($stack)-1];

                if ($top[0] > $newDepth) {
                    $value .= $top[1];
                    array_pop($stack);
                    $top = $stack[count($stack)-1];
                    $depth = $top[0];
                }
            }

            $value .= $this->createElement($text, $prefix)."\n";
        }

        while ($stack) {
            list($d, $closing) = array_pop($stack);
            $value .= $closing; 
        }

        return $value;
    }
}
