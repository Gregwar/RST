<?php

namespace Gregwar\RST\Directives;

use Gregwar\RST\Directive;
use Gregwar\RST\Parser;
use Gregwar\RST\Nodes\DummyNode;

class Dummy extends Directive
{
    public function getName()
    {
        return 'dummy';
    }

    public function processNode(Parser $parser, $variable, $data, array $options)
    {
        return new DummyNode(array('data' => $data, 'options' => $options));
    }
}
