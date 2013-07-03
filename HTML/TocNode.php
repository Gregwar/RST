<?php

namespace Gregwar\RST\HTML;

use Gregwar\RST\Nodes\TocNode as Base;

class TocNode extends Base
{
    protected function renderLevel($url, $titles, $level = 1)
    {
        if ($level > $this->depth) {
            return false;
        }

        $html = '';
        foreach ($titles as $entry) {
            list($title, $childs) = $entry;
            $html .= '<li><a href="'.$url.'">'.$title.'</a></li>';

            if ($childs) {
                $html .= '<ul>';
                $html .= $this->renderLevel($url, $childs, $level+1);
                $html .= '</ul>';
            }
        }

        return $html;
    }

    public function render()
    {
        $this->depth = isset($this->options['depth']) ? $this->options['depth'] : 2;

        $html = '<div class="toc"><ul>';
        foreach ($this->files as $file) {
            $reference = $this->environment->resolve($file);
            $html .= $this->renderLevel($reference['url'], $reference['titles']);
        }
        $html .= '</ul></div>';

        return $html;
    }
}
