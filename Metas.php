<?php

namespace Gregwar\RST;

class Metas
{
    protected $entries = array();

    public function __construct($entries)
    {
        if ($entries) {
            $this->entries = $entries;
        }
    }

    public function getAll()
    {
        return $this->entries;
    }

    /**
     * Sets the meta for url, giving the title, the modification time and
     * the dependencies list
     */
    public function set($file, $url, $title, $titles, $ctime, array $depends)
    {
        $this->entries[$file] = array(
            'file' => $file,
            'url' => $url,
            'title' => $title,
            'titles' => $titles,
            'ctime' => $ctime,
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
