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
function ciniki_wng_processors_html(&$ciniki, $tnid, &$request, $section) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();

    if( isset($section['settings']['html']) && $section['settings']['html'] != '' ) {
        $section['settings']['type'] = 'html';
        $section['settings']['sequence'] = $section['sequence'];
        $section['settings']['title_sequence'] = isset($section['title_sequence']) ? $section['title_sequence'] : 2;
        $section['settings']['class'] = 'section-' . ciniki_core_makePermalink($ciniki, $section['label']);
        $blocks[] = $section['settings'];
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
