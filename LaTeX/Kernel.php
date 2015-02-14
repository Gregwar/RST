<?php

namespace Gregwar\RST\LaTeX;

use Gregwar\RST\Kernel as Base;
use Gregwar\RST\LaTeX\Roles\DocRenderer;
use Gregwar\RST\Roles\DocProcessor;
use Gregwar\RST\Roles\RoleConfiguration;

class Kernel extends Base
{
    function getName()
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

    public function getRoleConfigurations()
    {
        return array(
            new RoleConfiguration('doc', new DocProcessor('doc'), new DocRenderer()),
            new RoleConfiguration('ref', new DocProcessor('ref'), new DocRenderer()),
        );
    }

    public function getFileExtension()
    {
        return 'tex';
    }
}
