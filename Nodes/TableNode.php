<?php

namespace Gregwar\RST\Nodes;

use Gregwar\RST\Parser;

abstract class TableNode extends Node
{
    protected $parts;
    protected $data = array();

    public function __construct($parts)
    {
        $this->parts = $parts;
        $this->data[] = array();
    }

    /**
     * Gets the columns count of the table
     */
    public function getCols()
    {
        return count($this->parts[1]);
    }

    /**
     * Gets the rows count of the table
     */
    public function getRows()
    {
        return count($this->data)-1;
    }

    public function push($parts, $line)
    {
        if ($parts) {
            if ($parts != $this->parts) {
                return false;
            }

            $this->data[] = array();
        } else {
            list($pretty, $parts) = $this->parts;
            $row = &$this->data[count($this->data)-1];

            for ($k=1; $k<=count($parts); $k++) {
                if ($k == count($parts)) {
                    $data = substr($line, $parts[$k-1]);
                } else {
                    $data = substr($line, $parts[$k-1], $parts[$k]-$parts[$k-1]);
                }

                if ($pretty) {
                    $data = substr($data, 0, -1);
                }

                $data = trim($data);

                if (isset($row[$k-1])) {
                    $row[$k-1] .= ' '.$data;
                } else {
                    $row[$k-1] = $data;
                }
            }
        }

        return true;
    }

    public function finalize(Parser $parser)
    {
        foreach ($this->data as &$row) {
            if ($row) {
                foreach ($row as &$col) {
                    $col = $parser->createSpan($col);
                }
            }
        }
    }
}
