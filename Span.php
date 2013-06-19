<?php

namespace Gregwar\RST;

class Span
{
    protected $parser;
    protected $span;

    public function __construct(Parser $parser, $span)
    {
        $this->parser = $parser;
        $this->span = $span;
    }

    /**
     * Renders the Span, which includes :
     *
     * - `verbatim`
     * - *italic*
     * - **bold**
     * - _underlined_
     * - |variable|
     */
    public function render()
    {
        $span = $this->span;

        if (is_array($span)) {
            $span = implode("\n", $span);
        }

        $prefix = sha1(time().'/'.mt_rand());
        $tokens = array();
        $span = preg_replace_callback('/`(.+)`/mUsi', function($match) use (&$tokens, $prefix) {
            $id = $prefix.count($tokens);
            $tokens[$id] = '<code>'.htmlspecialchars($match[1]).'</code>';

            return $id;
        }, $span);
        $span = preg_replace('/\*\*(.+)\*\*/mUsi', '<b>$1</b>', $span);
        $span = preg_replace('/\*(.+)\*/mUsi', '<em>$1</em>', $span);
        $span = preg_replace('/_(.+)_/mUsi', '<u>$1</u>', $span);

        foreach ($tokens as $id => $value) {
            $span = str_replace($id, $value, $span);
        }

        $environment = $this->parser->getEnvironment();
        $span = preg_replace_callback('/\|(.+)\|/mUsi', function($match) use ($environment) {
            return $environment->getVariable($match[1]);
        }, $span);

        return $span;
    }

    public function __toString()
    {
        return $this->render();
    }
}
