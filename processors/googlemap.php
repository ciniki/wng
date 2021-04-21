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
function ciniki_wng_processors_googlemap(&$ciniki, $tnid, &$request, $section) {

    $blocks = array();

    if( isset($section['settings']['latitude']) && $section['settings']['latitude'] != ''
        && isset($section['settings']['longitude']) && $section['settings']['longitude'] != '' 
        ) {
        $section['settings']['type'] = 'googlemap';
        $blocks[] = $section['settings'];
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
