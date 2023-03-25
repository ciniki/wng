<?php
//
// Description
// -----------
// Pick a month and year
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_monthpicker(&$ciniki, $tnid, $request, $block) {

    $content = '';

    $content .= "<div class='block-monthpicker"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";

    //
    // Display previous button
    //
    $content .= "<div class='button-wrap prev'>";
    $content .= "<a href='{$block['prev-url']}'>";
    $content .= "<div class='button'>";
    $content .= '<svg viewBox="0 0 80 80" stroke="#fff" fill="none"><polyline stroke-width="5" stroke-linecap="round" stroke-linejoin="round" points="50,70 20,40 50,10"/></svg>';
    $content .= "</div>";
    $content .= "</a>";
    $content .= '</div>';

    //
    // Display month and year
    //
    $content .= "<div class='date'>"
        . $block['month-year']
        . "</div>";

    //
    // Display next button
    //
    $content .= "<div class='button-wrap next'>";
    $content .= "<a href='{$block['next-url']}'>";
    $content .= "<div class='button'>";
    $content .= '<svg viewBox="0 0 80 80" stroke="#fff" fill="none"><polyline stroke-width="5" stroke-linecap="round" stroke-linejoin="round" points="30,70 60,40 30,10"/></svg>';
    $content .= "</div>";
    $content .= "</a>";
    $content .= '</div>';
    

    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';

    return array('stat'=>'ok', 'content'=>$content);
}
?>
