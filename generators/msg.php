<?php
//
// Description
// -----------
// Display an error message
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_msg(&$ciniki, $tnid, $request, $block) {

    $content = '';

    if( isset($block['content']) && $block['content'] != '' ) {      
        $content .= "<div class='block-msg limit-width center"
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . (isset($block['level']) && $block['level'] != '' ? ' ' . $block['level'] : '')
            . "'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";
        
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
        $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['content']);   
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['content']) && $rc['content'] != '' ) {
            $content .= $rc['content'];
        }

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
