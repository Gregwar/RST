<?php

namespace Gregwar\RST;

class Metas
{
    protected $entries = array();

    /**
     * Sets the meta for url, giving the title, the modification time and
     * the dependencies list
     */
    public function set($url, $title, $mtime, array $depends)
    {
        $this->entries[$url] = array(
            'url' => $url,
            'title' => $title,
            'mtime' => $mtime,
            'depends' => $depends
        );
    }

    /**
     * Gets the meta for a given document reference url
     */
    public function get($url)
    {
        if (isset($this->entries[$url])) {
            return $this->entries[$url];
        } else {
            return null;
        }
    }
}
