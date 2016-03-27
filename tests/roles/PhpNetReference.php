<?php

namespace Gregwar\RST\Tests\Roles;

use Gregwar\RST\Reference;
use Gregwar\RST\Environment;

class PhpNetReference extends Reference
{
    public function getName()
    {
        return 'method';
    }

    public function resolve(Environment $environment, $data)
    {
        $end = substr($data, -2);
        $url = $data;

        if ($end == '()') {
            $url = substr($data, 0, -2);
        }

        return array(
            'title' => $data,
            'url' => 'http://php.net/'.$url
        );
    }
};
