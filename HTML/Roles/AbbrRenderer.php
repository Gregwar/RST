<?php

namespace Gregwar\RST\HTML\Roles;

use Gregwar\RST\Parser;
use Gregwar\RST\Roles\Abbr;
use Gregwar\RST\Roles\Exception\InvalidArgumentException;
use Gregwar\RST\Roles\Role;
use Gregwar\RST\Roles\RoleRenderer;

class AbbrRenderer implements RoleRenderer
{
    public function render(Role $role, Parser $parser)
    {
        InvalidArgumentException::assert('role', $role, 'Gregwar\RST\Roles\Abbr');

        /** @var Abbr $role */
        return sprintf(
            '<abbr title="%s">%s</abbr>',
            htmlentities($role->description),
            htmlentities($role->abbreviation)
        );
    }
}
