<?php
//
// Description
// -----------
// This function will generate the HTML for the cart/account/login/logout buttons.
// These are typically at the very top of the page above the menu.
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

        $content .= "<span class='buttons'>";
        foreach($block['data'] as $button) {
            $content .= "<span class='" . (isset($button['class']) ? $button['class'] : '') . "'>";
            $content .= "<a href='" . $button['url'] . "'>" . $button['label'] . "</a>";
            $content .= "</span>";
        }
        $content .= "</span>";

        if( isset($block['social-icons']) && count($block['social-icons']) > 0 ) {
            $content .= "<span class='icons'>";
            foreach($block['social-icons'] as $icon) {
                if( $icon['type'] == 'facebook' ) {
                    $content .= "<span class='icon'><a target='_blank' href='" . $icon['url'] . "'>"
                        . "<i class='fab fa-facebook-f'></i>"
                        . "</a></span>";
                } elseif( $icon['type'] == 'instagram' ) {
                    $content .= "<span class='icon'><a target='_blank' href='" . $icon['url'] . "'>"
                        . '<i class="fab fa-instagram"></i>'
                        . "</a></span>";
                } elseif( $icon['type'] == 'twitter' ) {
                    $content .= "<span class='icon'><a target='_blank' href='" . $icon['url'] . "'>"
                        . "<i class='fab fa-twitter'></i>"
                        . "</a></span>";
                }
            }
            $content .= "</span>";
        }

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
