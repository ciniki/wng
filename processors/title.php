<?php
//
// Description
// -----------
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_processors_title(&$ciniki, $tnid, &$request, $section) {

    $blocks = array();

    if( isset($section['settings']['title']) && $section['settings']['title'] != '' ) {
        $section['settings']['type'] = 'title';
        $blocks[] = $section['settings'];
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
