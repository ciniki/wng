<?php
//
// Description
// -----------
// Generate a h1 title at the top of the page
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_title(&$ciniki, $tnid, $request, $block) {

    $content = '';

    if( isset($block['title']) && $block['title'] != '' ) { 
        $content .= "<div class='block-title"
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . "'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";

        if( isset($block['level']) && $block['level'] == 2 ) {
            $content .= "<h2>" . $block['title'] . "</h2>";
        } else {
            $content .= "<h1>" . $block['title'] . "</h1>";
        }

        if( isset($block['subtitle']) && $block['subtitle'] != '' ) {
            if( isset($block['level']) && $block['level'] == 2 ) {
                $content .= "<h3>" . $block['subtitle'] . "</h3>";
            } else {
                $content .= "<h2>" . $block['subtitle'] . "</h2>";
            }
        }

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
