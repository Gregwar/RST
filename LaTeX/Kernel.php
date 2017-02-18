<?php

namespace Gregwar\RST\LaTeX;

use Gregwar\RST\Kernel as Base;

class Kernel extends Base
{
    public function getName()
    {
        return 'LaTeX';
    }

    public function getDirectives()
    {
        $directives = parent::getDirectives();

        $directives = array_merge($directives, array(
            new Directives\LaTeXMain,
            new Directives\Image,
            new Directives\Meta,
            new Directives\Stylesheet,
            new Directives\Title,
            new Directives\Url,
            new Directives\Wrap('note')
        ));

        return $directives;
    }

    public function getFileExtension()
    {
        return 'tex';
    }
}
