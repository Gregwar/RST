<?php

namespace Gregwar\RST\HTML\Roles;

use Gregwar\RST\Parser;
use Gregwar\RST\Roles\Doc;
use Gregwar\RST\Roles\Exception\InvalidArgumentException;
use Gregwar\RST\Roles\Role;
use Gregwar\RST\Roles\RoleRenderer;

class DocRenderer implements RoleRenderer
{
    public function render(Role $role, Parser $parser)
    {
        InvalidArgumentException::assert('role', $role, 'Gregwar\RST\Roles\Doc');

        /** @var Doc $role */
        return sprintf(
            '<a href="%s%s">%s</a>',
            htmlentities($role->reference->url),
            $role->anchor ? '#' . htmlentities($role->anchor) : '',
            htmlentities($role->text ?: $role->reference->title ?: '')
        );
    }
}
