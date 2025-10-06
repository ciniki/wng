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
function ciniki_wng_generators_tablefilter(&$ciniki, $tnid, $request, $block) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');

    $content = '';

    $id = isset($block['id']) ? $block['id'] : (isset($block['sequence']) ? $block['sequence'] : 1);
        
    $content .= "<div class='block-tablefilter search"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";

    $content .= "<div class='search-label'>"
        . (isset($block['label']) ? $block['label'] : 'Search: ')
        . "</div>";
    $content .= "<div class='search-input'>"
        . "<input id='tablefilter-{$id}' onkeyup='C.tablefilter.update(\"{$id}\");'>"
//        . "<input id='livesearch-{$id}' onkeyup='C.tablefilter.update(\"{$id}\");'>"
        . "</div>";
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';

    //
    // The search hands back blocks html which will be placed inside this div
    //
//    $content .= "<div id='livesearch-results-{$id}' class='block-livesearch results'>"
//        . "</div>";

    //
    // Initialize livesearch with arguments provided
    //
    $js = "window.addEventListener('load',(e)=>{C.tablefilter.init(e,{$id},\"{$block['selector']}\");});";

    return array('stat'=>'ok', 'content'=>$content, 'js'=>$js);
}
?>
