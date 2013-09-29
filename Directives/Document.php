<?php

namespace Gregwar\RST\Directives;

use Gregwar\RST\Parser;
use Gregwar\RST\Directive;

use Gregwar\RST\Nodes\DocumentNode;

/**
 * Tell that this document should be latex compilable
 */
class Document extends Directive
{
    public function getName()
    {
        return 'document';
    }

    public function processNode(Parser $parser, $variable, $data, array $options)
    {
        return new DocumentNode;
    }
}
