<?php

namespace Gregwar\RST\HTML;

use Gregwar\RST\HTML\Roles\AbbrRenderer;
use Gregwar\RST\HTML\Roles\DocRenderer;
use Gregwar\RST\Kernel as Base;
use Gregwar\RST\Roles\AbbrProcessor;
use Gregwar\RST\Roles\DocProcessor;
use Gregwar\RST\Roles\RoleConfiguration;

class Kernel extends Base
{
    function getName()
    {
        return 'HTML';
    }

    public function getDirectives()
    {
        $directives = parent::getDirectives();

        $directives = array_merge($directives, array(
            new Directives\Image,
            new Directives\Meta,
            new Directives\Stylesheet,
            new Directives\Title,
            new Directives\Url,
            new Directives\Div,
            new Directives\Wrap('note')
        ));

        return $directives;
    }

    public function getRoleConfigurations()
    {
        return array(
            new RoleConfiguration('abbr', new AbbrProcessor(), new AbbrRenderer()),
            new RoleConfiguration('doc', new DocProcessor('doc'), new DocRenderer()),
            new RoleConfiguration('ref', new DocProcessor('ref'), new DocRenderer()),
        );
    }

    public function getFileExtension()
    {
        return 'html';
    }
}
