<?php

namespace Gregwar\RST\HTML\References;

use Gregwar\RST\Reference;
use Gregwar\RST\Environment;

class Doc implements Reference
{
    public function getName()
    {
        return 'doc';
    }

    public function resolve(Environment $environment, $data)
    {
        $metas = $environment->getMetas();
        $file = $environment->canonicalUrl($data);

        $entry = $metas->get($file);
        $entry['url'] = $environment->relativeUrl($entry['url']);

        return $entry;
    }
}
