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
        return '<b>'.$text.'</b>';
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
        return '<a href="'.htmlspecialchars($url).'">'.htmlspecialchars($title).'</a>';
    }

    public function reference($reference, $value)
    {
        if ($reference) {
            $text = $value['text'] ?: (isset($reference['title']) ? $reference['title'] : '');
            $url = $reference['url'];
            if ($value['anchor']) {
                $url .= '#' . $value['anchor'];
            }
            $link = $this->link($url, trim($text));
        } else {
            $link = $this->link('#', '(unresolved reference)');
        }

        return $link;
    }

    /**
     * Renders the Span, which includes :
     *
     * - ``literal``
     * - *italic*
     * - **bold**
     * - |variable|
     */
    public function render()
    {
        $self = $this;
        $environment = $this->parser->getEnvironment();
        $span = htmlspecialchars($this->span);

        // Emphasis
        $span = preg_replace_callback('/\*\*(.+)\*\*/mUsi', function ($matches) use ($self) {
          return $self->strongEmphasis($matches[1]);
        }, $span);
        $span = preg_replace_callback('/\*(.+)\*/mUsi', function ($matches) use ($self) {
          return $self->emphasis($matches[1]);
        }, $span);

        // Nbsp
        $span = preg_replace('/~/', $this->nbsp(), $span);
        
        // Replacing variables
        $span = preg_replace_callback('/\|(.+)\|/mUsi', function($match) use ($environment) {
            return $environment->getVariable($match[1]);
        }, $span);

        // Adding brs when a space is at the end of a line
        $span = preg_replace('/ \n/', $this->br(), $span);

        // Replacing tokens
        foreach ($this->tokens as $id => $value) {
            switch ($value['type']) {
            case 'literal':
                $span = str_replace($id, $this->literal($value['text']), $span);
                break;
            case 'reference':
                $reference = $environment->resolve($value['section'], $value['url']);
                $link = $this->reference($reference, $value);

                $span = str_replace($id, $link, $span);
                break;
            case 'link':
                if ($value['url']) {
                    $url = $environment->relativeUrl($value['url']);
                } else {
                    $url = $environment->getLink($value['link']);
                }
                $link = $this->link($url, $value['link']);
                $span = str_replace($id, $link, $span);
                break;
            }
        }

        return $span;
    }
}
