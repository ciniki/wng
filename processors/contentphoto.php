<?php
//
// Description
// -----------
// Process the section that will display a photo and paragraph.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_processors_contentphoto(&$ciniki, $tnid, &$request, $section) {

    $blocks = array();

    if( isset($section['settings']['content']) && $section['settings']['content'] != '' ) {
        $section['settings']['type'] = 'contentphoto';
        $section['settings']['sequence'] = $section['sequence'];
        $blocks[] = $section['settings'];
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
