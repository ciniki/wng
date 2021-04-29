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
function ciniki_wng_generators_html(&$ciniki, $tnid, $request, $block) {

    $content = isset($block['html']) ? $block['html'] : '';

    if( isset($block['js']) ) {
        return array('stat'=>'ok', 'content'=>$content, 'js'=>$block['js']);
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
