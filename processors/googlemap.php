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

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();

    if( isset($section['settings']['latitude']) && $section['settings']['latitude'] != ''
        && isset($section['settings']['longitude']) && $section['settings']['longitude'] != '' 
        ) {
        $section['settings']['type'] = 'googlemap';
        $section['settings']['class'] = 'section-' . ciniki_core_makePermalink($ciniki, $section['label']);
        $blocks[] = $section['settings'];
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
