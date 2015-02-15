<?php

namespace Gregwar\RST\LaTeX\Roles;

use Gregwar\RST\Parser;
use Gregwar\RST\Roles\Reference;
use Gregwar\RST\Roles\Exception\InvalidArgumentException;
use Gregwar\RST\Roles\Role;
use Gregwar\RST\Roles\RoleRenderer;

class ReferenceRenderer implements RoleRenderer
{
    public function render(Role $role, Parser $parser)
    {
        InvalidArgumentException::assert('role', $role, 'Gregwar\RST\Roles\Reference');

        /** @var Reference $role */
        if ($role->reference) {
            $refDoc = $role->reference->file ?: $parser->getEnvironment()->getUrl();

            return sprintf('\ref{%s%s}', $refDoc, $role->anchor ? "#$role->anchor" : '');
        } else {
            return '\href{#}{(unresolved reference)}';
        }
    }
}
