<?php

namespace Gregwar\RST\Roles;

class Abbr implements Role
{
    /**
     * @var string
     */
    public $abbreviation;

    /**
     * @var string
     */
    public $description;

    /**
     * @param string $abbreviation
     * @param string $description
     */
    public function __construct($abbreviation, $description)
    {
        $this->abbreviation = $abbreviation;
        $this->description = $description;
    }
}
