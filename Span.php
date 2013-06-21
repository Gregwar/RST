<?php

namespace Gregwar\RST;

class Span
{
    protected $parser;
    protected $span;
    protected $tokens;

    public function __construct(Parser $parser, $span)
    {
        if (is_array($span)) {
            $span = implode("\n", $span);
        }
        
        // Replacing literal with tokens
        $prefix = sha1(time().'/'.mt_rand());
        $tokens = array();
        $span = preg_replace_callback('/``(.+)``/mUsi', function($match) use (&$tokens, $prefix) {
            $id = $prefix.'/'.sha1($match[1]);
            $tokens[$id] = '<code>'.htmlspecialchars($match[1]).'</code>';

            return $id;
        }, $span);
        
        $environment = $parser->getEnvironment();

        // Replacing numbering
        foreach ($environment::$letters as $letter => $level) {
            $span = preg_replace_callback('/\#\\'.$letter.'/mUsi', function($match) use ($environment, $level) {
                return $environment->getNumber($level);
            }, $span);
        }

        $this->tokens = $tokens;
        $this->parser = $parser;
        $this->span = $span;

        // Signaling anonymous names
        $environment->resetAnonymousStack();
        if (preg_match_all('/(([a-z0-9]+)|(`(.+)`))__/mUsi', $span, $matches)) {
            foreach ($matches[2] as $k => $y) {
                $name = $matches[2][$k] ?: $matches[4][$k];
                $environment->pushAnonymous($name);
            }
        }
    }

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
            $span = str_replace($id, $value, $span);
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
