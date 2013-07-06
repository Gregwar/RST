<?php

namespace Gregwar\RST\HTML;

use Gregwar\RST\Document as Base;

use Gregwar\RST\Nodes\RawNode;

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
        $document .= "</html>\n";

        return $document;
    }

    public function addCss($css)
    {
        $environment = $this->getEnvironment();
        $css = $environment->relativeUrl($css);

        $this->addHeaderNode(new RawNode('<link rel="stylesheet" type="text/css" href="'.htmlspecialchars($css).'" />'));
    }

    public function addJs($js)
    {
        $environment = $this->getEnvironment();
        $js = $environment->relativeUrl($js);

        $this->addHeaderNode(new RawNode('<script type="text/javascript" src="'.htmlspecialchars($js).'"></script>'));
    }

    public function addFavicon($url = '/favicon.ico')
    {
        $environment = $this->getEnvironment();
        $url = $environment->relativeUrl($url);

        $this->addHeaderNode(new RawNode('<link rel="icon" type="image/x-icon" href="'.htmlspecialchars($url).'" />'));
    }
}
