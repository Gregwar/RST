<?php

namespace Gregwar\RST\Roles;

class DocReference
{
    /**
     * @var string|null This document's file reference (eg. 'index').
     */
    public $file;

    /**
     * @var string This document's URL (eg. 'index.html').
     */
    public $url;

    /**
     * @var string Title of this document.
     */
    public $title;

    /**
     * @var array[]|null An array of arrays containing `[0 => string $title, 1 => array $subtitles]`.
     */
    public $titles;

    /**
     * @var string[][]|null An array of arrays containing strings of references to other documents (eg. ['introduction','subdir/index']).
     */
    public $tocs;

    /**
     * @var int|null The time (UNIX timestamp) this document was last changed.
     */
    public $ctime;

    /**
     * @var string[]|null An array of strings of references to other documents (eg. ['introduction','subdir/index']).
     */
    public $depends;

    public static function fromReferenceArray(array $reference)
    {
        $docReference = new self;
        $docReference->file = isset($reference['file']) ? $reference['file'] : null;
        $docReference->url = $reference['url'];
        $docReference->title = $reference['title'];
        $docReference->titles = isset($reference['titles']) ? $reference['titles'] : null;
        $docReference->tocs = isset($reference['tocs']) ? $reference['tocs'] : null;
        $docReference->ctime = isset($reference['ctime']) ? $reference['ctime'] : null;
        $docReference->depends = isset($reference['depends']) ? $reference['depends'] : null;

        return $docReference;
    }

    final private function __construct()
    {
    }
}
