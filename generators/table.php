<?php
//
// Description
// -----------
// table
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_table(&$ciniki, $tnid, $request, $block) {

    $content = '';

        
    $content .= "<div class='block-table"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";

    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }

    $content .= "<div class='table'>";
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
