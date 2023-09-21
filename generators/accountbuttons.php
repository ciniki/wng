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
                    $content .= "<span class='icon'><a target='_blank' aria-label='Facebook' name='Facebook' href='" . $icon['url'] . "'>"
                        . "<i class='fab fa-facebook-f'></i>"
                        . "</a></span>";
                } elseif( $icon['type'] == 'instagram' ) {
                    $content .= "<span class='icon'><a target='_blank' aria-label='Instagram' name='Instagram' href='" . $icon['url'] . "'>"
                        . '<i class="fab fa-instagram"></i>'
                        . "</a></span>";
                } elseif( $icon['type'] == 'twitter' ) {
                    $content .= "<span class='icon'><a target='_blank' aria-label='Twitter' name='Twitter' href='" . $icon['url'] . "'>"
//                        . "<i class='fab fa-twitter'></i>"
                        . '<svg viewBox="0 0 24 24" aria-hidden="true" class="fab xtwitter"><g><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path></g></svg>'
                        . "</a></span>";
                } elseif( $icon['type'] == 'youtube' ) {
                    $content .= "<span class='icon'><a target='_blank' aria-label='YouTube' name='YouTube' href='" . $icon['url'] . "'>"
                        . "<i class='fab fa-youtube'></i>"
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
