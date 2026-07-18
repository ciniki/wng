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
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'socialIconsGenerate');
            $rc = ciniki_wng_socialIconsGenerate($ciniki, $tnid, $request, $block['social-icons']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( isset($rc['content']) && $rc['content'] != '' ) {
                $content .= "<span class='icons'>" . $rc['content'] . "</span>";
            }

/*            $content .= "<span class='icons'>";
            foreach($block['social-icons'] as $icon) {
                if( $icon['type'] == 'facebook' ) {
                    $content .= "<span class='icon'><a target='_blank' aria-label='Facebook' name='Facebook' href='" . $icon['url'] . "'>"
                        . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="svg facebook"><path d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"/></svg>'
//                        . "<i class='fab fa-facebook-f'></i>"
                        . "</a></span>";
                } elseif( $icon['type'] == 'instagram' ) {
                    $content .= "<span class='icon'><a target='_blank' aria-label='Instagram' name='Instagram' href='" . $icon['url'] . "'>"
                        . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 424 512" class="svg instagram"><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg>'
//                        . '<i class="fab fa-instagram"></i>'
                        . "</a></span>";
                } elseif( $icon['type'] == 'twitter' ) {
                    $content .= "<span class='icon'><a target='_blank' aria-label='Twitter' name='Twitter' href='" . $icon['url'] . "'>"
//                        . "<i class='fab fa-twitter'></i>"
                        . '<svg viewBox="0 0 24 24" aria-hidden="true" class="svg xtwitter"><g><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path></g></svg>'
                        . "</a></span>";
                } elseif( $icon['type'] == 'youtube' ) {
                    $content .= "<span class='icon'><a target='_blank' aria-label='YouTube' name='YouTube' href='" . $icon['url'] . "'>"
                        . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="svg youtube"><path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z"/></svg>'
//                        . "<i class='fab fa-youtube'></i>"
                        . "</a></span>";
                }
            }
            $content .= "</span>"; */
        }

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
