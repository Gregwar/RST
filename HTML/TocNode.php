<?php

namespace Gregwar\RST\HTML;

use Gregwar\RST\Nodes\TocNode as Base;

class TocNode extends Base
{
    protected function renderLevel($url, $titles, $level = 1, $path = array())
    {
        if ($level > $this->depth) {
            return false;
        }

        $html = '';
        foreach ($titles as $k => $entry) {
            $path[$level-1] = $k+1;
            list($title, $childs) = $entry;
            $token = 'title.'.implode('.', $path);
            $html .= '<li><a href="'.$url.'#'.$token.'">'.$title.'</a></li>';

            if ($childs) {
                $html .= '<ul>';
                $html .= $this->renderLevel($url, $childs, $level+1, $path);
                $html .= '</ul>';
            }
        }

        return $html;
    }

    public function render()
    {
        if (isset($this->options['hidden'])) {
            return '';
        }

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
