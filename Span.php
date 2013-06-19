<?php

namespace Gregwar\RST;

class Span
{
    protected $span;

    public function __construct(Parser $parser, $span)
    {
        $this->span = $span;
    }

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

        return $span;
    }

    public function __toString()
    {
        return $this->render();
    }
}
