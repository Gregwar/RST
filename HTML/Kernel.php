<?php

namespace Gregwar\RST\HTML;

use Gregwar\RST\Kernel as Base;

class Kernel extends Base
{
    public function getName()
    {
        return 'HTML';
    }

    public function getDirectives()
    {
        $directives = parent::getDirectives();

        $directives = array_merge($directives, array(
            new Directives\Image,
            new Directives\Figure,
            new Directives\Meta,
            new Directives\Stylesheet,
            new Directives\Title,
            new Directives\Url,
            new Directives\Div,
            new Directives\Wrap('note')
        ));

        return $directives;
    }

    public function getFileExtension()
    {
        return 'html';
    }
}
