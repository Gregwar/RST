<?php

namespace Gregwar\RST\LaTeX;

use Gregwar\RST\Document as Base;

use Gregwar\RST\Nodes\RawNode;
use Gregwar\RST\LaTeX\Nodes\LaTeXNode;

class Document extends Base
{
    public function render()
    {
        $document = '';
        foreach ($this->nodes as $node) {
            $document .= $node->render() . "\n";
        }

        return $document;
    }

    public function renderDocument()
    {
        $isLatex = count($this->getNodes(function($node) {
            return $node instanceof DocumentNode;
        })) != 0;

        $document = '';

        if ($isLatex) {
            $document .= "\\documentclass[11pt]{report}\n";
            $document .= "\\usepackage[utf8]{inputenc}\n";
            $document .= "\\usepackage[T1]{fontenc}\n";
            $document .= "\\usepackage[french]{babel}\n";
            $document .= "\\usepackage{cite}\n";
            $document .= "\\usepackage{amssymb}\n";
            $document .= "\\usepackage{amsmath}\n";
            $document .= "\\usepackage{mathrsfs}\n";
            $document .= "\\usepackage{graphicx}\n";
            $document .= "\\usepackage{hyperref}\n";
            $document .= "\\usepackage{listings}\n";

            foreach ($this->headerNodes as $node) {
                $document .= $node->render()."\n";
            }
            $document .= "\\begin{document}\n";
        }

        $document .= "\label{".$this->environment->getUrl()."}\n";
        $document .= $this->render();

        if ($isLatex) {
            $document .= "\\end{document}\n";
        }

        return $document;
    }
}
