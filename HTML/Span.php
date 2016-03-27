<?php

namespace Gregwar\RST\HTML;

use Gregwar\RST\Span as Base;

class Span extends Base
{
    public function emphasis($text)
    {
        return '<em>'.$text.'</em>';
    }

    public function strongEmphasis($text)
    {
        return '<strong>'.$text.'</strong>';
    }

    public function nbsp()
    {
        return '&nbsp;';
    }

    public function br()
    {
        return '<br />';
    }

    public function literal($text)
    {
        return '<code>'.$text.'</code>';
    }

    public function link($url, $title)
    {
        return '<a href="'.htmlspecialchars($url).'">'.$title.'</a>';
    }

    public function escape($span)
    {
        return htmlspecialchars($span);
    }
}
