<?php

namespace Gregwar\RST\Nodes;

abstract class CodeNode extends BlockNode
{
    protected $raw = false;
    protected $language = null;

    public function setLanguage($language = null)
    {
        $this->language = $language;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setRaw($raw)
    {
        $this->raw = $raw;
    }
}
