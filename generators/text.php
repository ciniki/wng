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
function ciniki_wng_generators_text(&$ciniki, $tnid, $request, $block) {

    $content = '';

    //
    // Make sure there is content to edit
    //
    if( isset($block['content']) && $block['content'] != '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
        $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['content']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( $rc['content'] != '' ) {
            $content .= "<div class='block-text"
                . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
                . "'>";
            $content .= "<div class='wrap'>";
            $content .= "<div class='content'>";

            if( isset($block['title']) && $block['title'] != '' ) {
                $content .= "<h2>" . $block['title'] . "</h2>";
            }

            $content .= $rc['content'];

            $content .= '</div>';
            $content .= '</div>';
            $content .= '</div>';
        }
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
