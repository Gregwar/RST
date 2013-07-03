<?php

namespace Gregwar\RST\HTML;

use Gregwar\RST\Nodes\TableNode as Base;

class TableNode extends Base
{
    public function render()
    {
        $html = '<table>';
        foreach ($this->data as &$row) {
            if (!$row) {
                continue;
            }

            $html .= '<tr>';
            foreach ($row as &$col) {
                $html .= '<td>';
                $html .= $col->render();
                $html .= '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';

        return $html;
    }
}
