<?php
//
// Description
// -----------
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_wng_generators_socialicons(&$ciniki, $tnid, $request, $block) {

    $content = '';

    //
    // Make sure there is content to edit
    //
    if( (isset($block['content']) && $block['content'] != '')
        || (isset($block['icons']) && count($block['icons']) > 0)  
        ) {

        $content .= "<div class='block-socialicons"
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . ' showat-' . (isset($block['toggle-em']) && $block['toggle-em'] != '' ? $block['toggle-em'] : '60') . '-em'
            . "'>";
        $content .= "<div class='wrap'>";

        //
        // Add content
        //
        if( isset($block['content']) && $block['content'] != '' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['content']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( $rc['content'] != '' ) {
                $content .= '<div class="content"><div class="content-wrap">' . $rc['content'] . '</div></div>';
            }
        }

        //
        // Add icons 
        //
        if( isset($block['icons']) && count($block['icons']) > 0 ) {
            $content .= '<div class="icons">';
            foreach($block['icons'] as $icon) {
                // **NOTE** also in generators_accountbuttons
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
                } elseif( $icon['type'] == 'youtube' ) {
                    $content .= "<span class='icon'><a target='_blank' href='" . $icon['url'] . "'>"
                        . "<i class='fab fa-youtube'></i>"
                        . "</a></span>";
                }
            }
            $content .= '</div>';
        }

        $content .= '</div>';
        $content .= '</div>';
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
