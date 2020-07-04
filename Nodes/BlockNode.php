<?php

namespace Gregwar\RST\Nodes;

abstract class BlockNode extends Node
{
    private $startingLineNumber = 0;

    public function __construct(array $lines)
    {
        if (count($lines)) {
            $firstLine = $lines[0];
            for ($k=0; $k<strlen($firstLine); $k++) {
                if (trim($firstLine[$k])) {
                    break;
                }
            }

            foreach ($lines as &$line) {
                $line = substr($line, $k);
            }
        }

        $this->value = implode("\n", $lines);
    }

    public function setStartingLineNumber($startingLineNumber)
    {
        $this->startingLineNumber = $startingLineNumber;
    }

    public function getStartingLineNumber()
    {
        return $this->startingLineNumber;
    }
}
