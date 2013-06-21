<?php

namespace Gregwar\RST\Directives;

use Gregwar\RST\Span;
use Gregwar\RST\Parser;
use Gregwar\RST\SubDirective;

use Gregwar\RST\Nodes\WrapperNode;

/**
 * Wraps the block that follows in a div with class "note"
 *
 * .. note:: 
 *      This is an important note !
 */
class Note extends SubDirective
{
    public function getName()
    {
        return 'note';
    }

    public function processSub(Parser $parser, $document, $variable, $data, array $options)
    {
        return new WrapperNode($document, '<div class="note">', '</div>');
    }
}
