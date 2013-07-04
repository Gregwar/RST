<?php

namespace Gregwar\RST\HTML;

use Gregwar\RST\Factory as Base;

class Factory extends Base
{
    function getName()
    {
        return 'HTML';
    }

    public function getDirectives()
    {
        return array(
            new Directives\CodeBlock,
            new Directives\Image,
            new Directives\Meta,
            new Directives\Replace,
            new Directives\Stylesheet,
            new Directives\Title,
            new Directives\Wrap('note'),
            new \Gregwar\RST\Directives\Toctree
        );
    }
}
