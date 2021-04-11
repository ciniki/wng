<?php
//
// Description
// -----------
// This function will generate the HTML for the cart/account/login/logout buttons
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_generators_accountbuttons(&$ciniki, $tnid, $request, $block) {

    $content = '';

    if( isset($block['data']) && is_array($block['data']) && count($block['data']) > 0 ) {
        $content .= "<div class='block-accountbuttons"
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . (isset($block['toggle-em']) && $block['toggle-em'] != '' ? ' showat-' . $block['toggle-em'] . '-em': '')
            . "'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";

        foreach($block['data'] as $button) {
            $content .= "<span class='" . (isset($button['class']) ? $button['class'] : '') . "'>";
            $content .= "<a href='" . $button['url'] . "'>" . $button['label'] . "</a>";
            $content .= "</span>";
        }

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
