<?php

namespace Gregwar\RST;

class Metas
{
    protected $entries = array();
    protected $parents = array();

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
    public function set($file, $url, $title, $titles, $tocs, $ctime, array $depends)
    {
        foreach ($tocs as $toc) {
            foreach ($toc as $child) {
                $this->parents[$child] = $file;
                if (isset($this->entries[$child])) {
                    $this->entries[$child]['parent'] = $file;
                }
            }
        }

        $this->entries[$file] = array(
            'file' => $file,
            'url' => $url,
            'title' => $title,
            'titles' => $titles,
            'tocs' => $tocs,
            'ctime' => $ctime,
            'depends' => $depends
        );

        if (isset($this->parents[$file])) {
            $this->entries[$file]['parent'] = $this->parents[$file];
        }
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
