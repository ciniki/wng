<?php
//
// Description
// -----------
// schedule
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_schedule(&$ciniki, $tnid, $request, $block) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');

    $content = '';

    $content .= "<div class='block-schedule"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . (isset($block['subtitle']) && $block['subtitle'] != '' ? ' subtitle' : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";

    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }
    if( isset($block['subtitle']) && $block['subtitle'] != '' ) {
        $content .= "<h3>" . $block['subtitle'] . "</h3>";
    }

    $content .= "<div class='timeslots'>";

    foreach($block['items'] as $timeslot) {
        $content .= "<div class='timeslot'>";
        $content .= "<div class='timetitle'>";
        $content .= "<div class='time'>" . str_replace(' ', '&nbsp;', $timeslot['time']) . "</div>";
        $content .= "<div class='title'>{$timeslot['title']}</div>";
        $content .= "</div>";
        if( isset($timeslot['synopsis']) && $timeslot['synopsis'] != '' ) {
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $timeslot['synopsis']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.243', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= "<div class='synopsis'>{$rc['content']}</div>";
        } else {
            $content .= "<div class='synopsis'></div>";
        }
        
        if( isset($block['details-columns']) && isset($timeslot['items']) && count($timeslot['items']) > 0 ) {
            $content .= "<div class='details-table'><table>";
            if( isset($block['details-headers']) && $block['details-headers'] == 'yes' ) {
                $content .= "<thead><tr>";
                foreach($block['details-columns'] as $col) {
                    $content .= "<th>{$col['label']}</th>";
                }
                $content .= "</tr></thead>";
            }
            $content .= "<tbody>";
            foreach($timeslot['items'] as $item) {
                $content .= "<tr>";
                foreach($block['details-columns'] as $col) {
                    $content .= "<td"
                        . (isset($col['class']) && $col['class'] != '' ? " class='{$col['class']}'" : '')
                        . ">";
                    if( isset($item[$col['field']]) ) {
                        $content .= $item[$col['field']];
                    }
                    $content .= "</td>";
                }
                $content .= "</tr>";
            }
            $content .= "</tbody>";
            $content .= "</table></div>";
        }

        $content .= "</div>";
    }



/*
    $content .= "<table>";
    $num_cols = 0;
    if( !isset($block['headers']) || $block['headers'] == 'yes' ) {
        $content .= "<thead><tr>";
        foreach($block['columns'] as $column) {
            $content .= "<th" . (isset($column['class']) && $column['class'] != '' ? " class='" . $column['class'] . "'" : "") . ">"
                . $column['label']
                . "</th>";
            $num_cols++;
        }
        $content .= "</tr></thead>";
    }
    $content .= "<tbody>";
    $count = 0;
    foreach($block['rows'] as $row) {
        $content .= "<tr>";
        $cnum = 1;
        foreach($block['columns'] as $column) {
            $cell_type = ($cnum == 1 && isset($block['headers']) && $block['headers'] == 'firstcolumn' ? 'th' : 'td');
            $cell_content = '';
            if( isset($column['fold-label']) && $column['fold-label'] != '' ) {
                $cell_content .= "<span class='fold-label'>" . $column['fold-label'] . "</span>";
            }
            if( isset($column['strsub']) && $column['strsub'] != '' ) {
                $value = $column['strsub'];
                if( preg_match('/{_([a-zA-Z0-9_]+)_}/', $column['strsub'], $m) ) {
                    foreach($m as $field) {
                        if( isset($row[$field]) ) {
                            $value = str_replace("{_{$field}_}", $row[$field], $value);
                        } 
                    }
                }
                $cell_content .= $value;
            }
            if( isset($column['field']) && isset($row[$column['field']]) ) {
                $cell_content .= $row[$column['field']];
            } 
            if( trim($cell_content) == '' ) {
                $column['class'] .= ($column['class'] != '' ? ' ' : '') . 'empty';
            }
            $content .= "<{$cell_type}" . (isset($column['class']) && $column['class'] != '' ? " class='" . $column['class'] . "'" : "") . ">";
            $content .= $cell_content;
            $content .= "</{$cell_type}>";
            $cnum++;
        }
        $content .= "</tr>";
        $count++;
    }
    if( $count == 0 && isset($block['empty']) ) {
        $content .= "<tr><td class='empty' colspan='" . $num_cols . "'>" . $block['empty'] . "</td></tr>";
    }
    $content .= "</tbody>";
    if( isset($block['footer']) && count($block['footer']) > 0 ) {
        $content .= "<tfoot><tr>";
        foreach($block['footer'] as $cell) {
            $content .= '<td'
                . (isset($cell['colspan']) && $cell['colspan'] != '' ? " colspan='{$cell['colspan']}'" : '')
                . (isset($cell['class']) && $cell['class'] != '' ? " class='{$cell['class']}'" : '')
                . '>';
            if( isset($cell['fold-label']) && $cell['fold-label'] != '' ) {
                $content .= "<span class='fold-label'>" . $cell['fold-label'] . "</span>";
            }
            $content .= $cell['value'];
            $content .= "</td>";
        }
        $content .= "</tr></tfoot>";
    }
    $content .= "</table>";

*/
    $content .= "</div>";

    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';

    if( isset($block['js']) && $block['js'] != '' ) {
        return array('stat'=>'ok', 'content'=>$content, 'js'=>$block['js']);
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
