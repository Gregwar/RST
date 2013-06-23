<?php

namespace Gregwar\RST\Directives;

use Gregwar\RST\Parser;
use Gregwar\RST\Directive;

use Gregwar\RST\Nodes\ImageNode;

/**
 * Renders an image, example :
 *
 * .. image:: image.jpg
 *      :width: 100
 *      :title: An image
 *
 */
class Image extends Directive
{
    public function getName()
    {
        return 'image';
    }

    public function processNode(Parser $parser, $variable, $data, array $options)
    {
        return new ImageNode($data, $options);
    }
}
