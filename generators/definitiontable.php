<?php
//
// Description
// -----------
// definitionlist
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_definitiontable(&$ciniki, $tnid, $request, $block) {

    $content = '';

        
    $content .= "<div class='block-definitiontable"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";

    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }

    $content .= "<div class='table'><table>";
    foreach($block['data'] as $row) {
        $content .= "<tr class='"
            . (isset($row['class']) && $row['class'] != '' ? ' ' . $row['class'] : '')
            . "'>";
        if( isset($row['label']) ) {
            $content .= "<th>{$row['label']}</th>";
        }
        if( isset($row['value']) ) {
            $content .= "<td>{$row['value']}</td>";
        }
        $content .= "</tr>";
    }
    $content .= "</table></div>";

    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';

    return array('stat'=>'ok', 'content'=>$content);
}
?>
