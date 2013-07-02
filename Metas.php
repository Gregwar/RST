<?php

namespace Gregwar\RST;

class Metas
{
    protected $entries = array();

    public function get($url)
    {
        if (isset($this->entries[$url])) {
            return $this->entries[$url];
        } else {
            return null;
        }
    }
}
