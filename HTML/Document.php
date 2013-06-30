<?php

namespace Gregwar\RST\HTML;

use Gregwar\RST\Document as Base;

class Document extends Base
{
    public function render()
    {
        $document = '';
        foreach ($this->nodes as $node) {
            $document .= $node->render()."\n\n";
        }

        return $document;
    }

    public function renderDocument()
    {
        $document = "<!DOCTYPE html>\n";
        $document .= "<html>\n";

        $document .= "<head>\n";
        $document .= "<meta charset=\"utf-8\" />\n";
        foreach ($this->headerNodes as $node) {
            $document .= $node->render()."\n";
        }
        $document .= "</head>\n";

        $document .= "<body>\n";
        $document .= $this->render();
        $document .= "</body>\n";
        $document .= "</html\n";

        return $document;
    }
}
