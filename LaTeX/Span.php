<?php

namespace Gregwar\RST\LaTeX;

use Gregwar\RST\Span as Base;

class Span extends Base
{
    public function emphasis($text)
    {
        return '\textit{'.$text.'}';
    }

    public function strongEmphasis($text)
    {
        return '\textbf{'.$text.'}';
    }

    public function nbsp()
    {
        return '~';
    }

    public function br()
    {
        return "\\\\\\\\\n";
    }

    public function literal($text)
    {
        return '\verb|'.$text.'|';
    }

    public function link($url, $title, $refDoc = '')
    {
        if (strlen($url) && $url[0] == '#') {
            if (!$refDoc) {
                $refDoc = $this->environment->getUrl();
            }
            $url = substr($url, 1);
            $url = $url ? '#'.$url : '';
            return '\ref{'.$refDoc.$url.'}';
        } else {
            return '\href{'.$url.'}{'.$title.'}';
        }
    }

    public function escape($span)
    {
        return $span;
    }
}
