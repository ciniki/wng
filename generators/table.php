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

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');

    $content = '';

        
    $content .= "<div class='block-table"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . (isset($block['form']) && $block['form'] != '' ? ' form' : '')
        . (isset($block['rows']) && (count($block['rows'])%2) == 0 ? ' q-2' : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $link_id = isset($block['title']) && $block['title'] != '' ? ciniki_core_makePermalink($ciniki, $block['title']) : '';
    $content .= "<div id='{$link_id}' class='content'>";

    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }
    if( isset($block['subtitle']) && $block['subtitle'] != '' ) {
        $content .= "<h3>" . $block['subtitle'] . "</h3>";
    }

    if( isset($block['content']) && $block['content'] != '' ) {
        $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['content']);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.246', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
        }
        $content .= "<div class='synopsis'>" . $rc['content'] . "</div>";
    }

    $content .= "<div class='table'>";
    if( isset($block['form']) && $block['form'] == 'yes' ) {    
        $content .= "<form action='' method='POST'>";
        if( isset($block['form-action']) && $block['form-action'] != '' ) {
            $content .= "<input type='hidden' name='action' value='{$block['form-action']}'>";
        }
    }
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
        $content .= "<tr"
            . (isset($row['cssclass']) && $row['cssclass'] != '' ? " class='{$row['cssclass']}'" : '')
            . ">";
        $cnum = 1;
        foreach($block['columns'] as $column) {
            $column_class = isset($column['class']) ? $column['class'] : '';
            $cell_type = ($cnum == 1 && isset($block['headers']) && $block['headers'] == 'firstcolumn' ? 'th' : 'td');
            $cell_content = '';
            if( isset($column['fold-label']) && $column['fold-label'] != '' ) {
                $cell_content .= "<span class='fold-label'>" . $column['fold-label'] . "</span><span class='cell-content'>";
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
            if( isset($column['fold-label']) && $column['fold-label'] != '' ) {
                $cell_content .= "</span>";
            }
            if( isset($column['info-field']) && isset($row[$column['info-field']]) && $row[$column['info-field']] != '' ) {
                $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $row[$column['info-field']]);
                if( $rc['stat'] == 'ok' ) {
                    $cell_content .= "<div class='extra-info'>" . $rc['content'] . '</div>';
                }
            }
            if( trim($cell_content) == '' ) {
                $column_class .= ($column_class != '' ? ' ' : '') . 'empty';
            }
            $content .= "<{$cell_type}" . ($column_class != '' ? " class='{$column_class}'" : "") . ">";
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
    if( isset($block['footers']) && count($block['footers']) > 0 ) {
        $content .= "<tfoot>";
        foreach($block['footers'] as $footer) {
            $content .= "<tr>";
            foreach($footer as $cell) {
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
            $content .= "</tr>";
        }
        $content .= "</tfoot>";
    }

    $content .= "</table>";
    if( isset($block['form']) && $block['form'] == 'yes' ) {    
        $content .= "<div class='form-buttons'>";
        if( isset($block['cancel-url']) && $block['cancel-url'] != '' ) {
            $content .= "<a class='button' href='{$block['cancel-url']}'>Cancel</a>";
        }
        $content .= "<input class='button' type='submit' value='Save'>";
        $content .= "</div>";
        $content .= "</form>";
    }
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
