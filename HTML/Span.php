<?php

namespace Gregwar\RST\HTML;

use Gregwar\RST\Span as Base;

class Span extends Base
{
    /**
     * Renders the Span, which includes :
     *
     * - ``verbatim``
     * - *italic*
     * - **bold**
     * - |variable|
     */
    public function render()
    {
        $environment = $this->parser->getEnvironment();
        $span = $this->span;

        // Emphasis
        $span = preg_replace('/\*\*(.+)\*\*/mUsi', '<b>$1</b>', $span);
        $span = preg_replace('/\*(.+)\*/mUsi', '<em>$1</em>', $span);

        // Replacing literal tokens
        foreach ($this->tokens as $id => $value) {
            if ($value['type'] == 'literal') {
                $span = str_replace($id, '<code>'.$value['text'].'</code>', $span);
            }
            if ($value['type'] == 'reference') {
                $reference = $environment->resolve($value['url']);

                if ($reference) {
                    $text = $value['text'] ?: $reference['title'];
                    $link = '<a href="'.$reference['url'].'">'.trim($text).'</a>';
                } else {
                    $link = '<a href="#">(unresolved reference)</a>';
                }
                $span = str_replace($id, $link, $span);
            }
        }
        
        // Replacing variables
        $span = preg_replace_callback('/\|(.+)\|/mUsi', function($match) use ($environment) {
            return $environment->getVariable($match[1]);
        }, $span);

        // Link callback
        $linkCallback = function($match) use ($environment) {
            $link = $match[2] ?: $match[4];

            if (preg_match('/^(.+) <(.+)>$/mUsi', $link, $match)) {
                $link = $match[1];
                $environment->setLink($link, $match[2]);
            }

            return '<a href="'.$environment->getLink($link).'">'.$link.'</a>';
        };
        
        // Replacing anonymous links
        $span = preg_replace_callback('/(([a-z0-9]+)|(`(.+)`))__/mUsi', $linkCallback, $span);

        // Replacing links
        $span = preg_replace_callback('/(([a-z0-9]+)|(`(.+)`))_/mUsi', $linkCallback, $span);

        // Adding brs when a space is at the end of a line
        $span = preg_replace('/ \n/', '<br />', $span);

        return $span;
    }

    public function __toString()
    {
        return $this->render();
    }
}
