<?php

namespace Gregwar\RST\HTML;

use Gregwar\RST\Span as Base;

class Span extends Base
{
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
        $environment = $this->parser->getEnvironment();
        $span = htmlspecialchars($this->span);

        // Emphasis
        $span = preg_replace('/\*\*(.+)\*\*/mUsi', '<b>$1</b>', $span);
        $span = preg_replace('/\*(.+)\*/mUsi', '<em>$1</em>', $span);
        
        // Replacing variables
        $span = preg_replace_callback('/\|(.+)\|/mUsi', function($match) use ($environment) {
            return $environment->getVariable($match[1]);
        }, $span);

        // Adding brs when a space is at the end of a line
        $span = preg_replace('/ \n/', '<br />', $span);

        // Replacing literal tokens
        foreach ($this->tokens as $id => $value) {
            switch ($value['type']) {
            case 'literal':
                $span = str_replace($id, '<code>'.$value['text'].'</code>', $span);
                break;
            case 'reference':
                $reference = $environment->resolve($value['url']);

                if ($reference) {
                    $text = $value['text'] ?: (isset($reference['title']) ? $reference['title'] : '');
                    $link = '<a href="'.$reference['url'].'">'.trim($text).'</a>';
                } else {
                    $link = '<a href="#">(unresolved reference)</a>';
                }
                $span = str_replace($id, $link, $span);
                break;
            case 'link':
                $url = $environment->getLink($value['link']);
                $link = '<a href="'.htmlspecialchars($url).'">'.$value['link'].'</a> ';
                $span = str_replace($id, $link, $span);
                break;
            }
        }

        return $span;
    }

    public function __toString()
    {
        return $this->render();
    }
}
