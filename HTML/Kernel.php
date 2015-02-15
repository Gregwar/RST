<?php

namespace Gregwar\RST\HTML;

use Gregwar\RST\HTML\Roles\AbbrRenderer;
use Gregwar\RST\Kernel as Base;
use Gregwar\RST\Roles\AbbrProcessor;
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
        $configs = array(
            new RoleConfiguration('abbr', new AbbrProcessor(), new AbbrRenderer())
        );

        return array_merge($configs, parent::getRoleConfigurations());
    }

    public function getFileExtension()
    {
        return 'html';
    }
}
