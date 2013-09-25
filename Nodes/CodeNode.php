<?php

namespace Gregwar\RST\Nodes;

abstract class CodeNode extends BlockNode
{
    protected $language = null;

    public function setLanguage($language = null)
    {
        $this->language = $language;
    }

    public function getLanguage()
    {
        return $this->language;
    }
}
