<?php

namespace Gregwar\RST\Roles;

use Gregwar\RST\Document;
use Gregwar\RST\Parser;
use Gregwar\RST\Roles\Exception\InvalidContentException;

class AbbrProcessor implements RoleProcessor
{
    public function getName()
    {
        return 'abbr';
    }

    public function process($content, Parser $parser)
    {
        // Matches '$1 ($2)'
        if (!preg_match('~^([^\\(]+)\s+(\\((.+)\\))$~', $content, $matches)) {
            throw new InvalidContentException(
                "Invalid abbreviation role content, expected 'abbreviation (title)', got '$content''"
            );
        }

        return new Abbr($matches[1], $matches[3]);
    }

    public function finalize(Role $role, Document $document)
    {
    }
}
