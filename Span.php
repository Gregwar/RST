<?php

namespace Gregwar\RST;

class Span
{
    protected $parser;
    protected $span;

    public function __construct(Parser $parser, $span)
    {
        if (is_array($span)) {
            $span = implode("\n", $span);
        }

        $this->parser = $parser;
        $this->span = $span;

        $environment = $parser->getEnvironment();

        // Signaling anonymous names
        if (preg_match('/(([a-z0-9]+)|(`(.+)`))__/mUsi', $span, $match)) {
            $name = $match[2] ?: $match[4];
            $environment->setAnonymousName($name);
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

        // Replacing literal with tokens
        $prefix = sha1(time().'/'.mt_rand());
        $tokens = array();
        $span = preg_replace_callback('/``(.+)``/mUsi', function($match) use (&$tokens, $prefix) {
            $id = $prefix.count($tokens);
            $tokens[$id] = '<code>'.htmlspecialchars($match[1]).'</code>';

            return $id;
        }, $span);

        // Emphasis
        $span = preg_replace('/\*\*(.+)\*\*/mUsi', '<b>$1</b>', $span);
        $span = preg_replace('/\*(.+)\*/mUsi', '<em>$1</em>', $span);

        // Replacing literal tokens
        foreach ($tokens as $id => $value) {
            $span = str_replace($id, $value, $span);
        }
        
        // Replacing variables
        $span = preg_replace_callback('/\|(.+)\|/mUsi', function($match) use ($environment) {
            return $environment->getVariable($match[1]);
        }, $span);
        
        // Replacing anonymous links
        $span = preg_replace_callback('/(([a-z0-9]+)|(`(.+)`))__/mUsi', function($match) use ($environment) {
            $link = $match[2] ?: $match[4];

            return '<a href="'.$environment->getLink($link).'">'.$link.'</a>';
        }, $span);

        // Replacing links
        $span = preg_replace_callback('/(([a-z0-9]+)|(`(.+)`))_/mUsi', function($match) use ($environment) {
            $link = $match[2] ?: $match[4];

            return '<a href="'.$environment->getLink($link).'">'.$link.'</a>';
        }, $span);

        return $span;
    }

    public function __toString()
    {
        return $this->render();
    }
}
