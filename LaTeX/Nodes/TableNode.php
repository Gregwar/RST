<?php

namespace Gregwar\RST\LaTeX\Nodes;

use Gregwar\RST\Nodes\TableNode as Base;

class TableNode extends Base
{
    public function render()
    {
        $cols = 0;

        $rows = array();
        foreach ($this->data as &$row) {
            if (!$row) {
                continue;
            }

            $rowTex = '';
            $cols = max($cols, count($row));

            foreach ($row as $n => &$col) {
                $rowTex .= $col->render();

                if ($n+1 < count($row)) {
                    $rowTex .= ' & ';
                }
            }
            $rowTex .= ' \\\\' . "\n";
            $rows[] = $rowTex;
        }
        $aligns = array();
        for ($i=0; $i<$cols; $i++) {
            $aligns[] = 'l';
        }

        $aligns = '|'.implode('|', $aligns).'|';
        $rows = "\\hline\n".implode("\\hline\n", $rows)."\\hline\n";

        $tex = "\\begin{tabular}{".$aligns."}\n".$rows."\n\\end{tabular}\n";

        return $tex;
    }
}
