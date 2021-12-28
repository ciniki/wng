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
function ciniki_wng_processors_text(&$ciniki, $tnid, &$request, $section) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();

    if( isset($section['settings']['content']) && $section['settings']['content'] != '' ) {
        $section['settings']['type'] = 'text';
        $section['settings']['sequence'] = $section['sequence'];
        $section['settings']['class'] = 'section-' . ciniki_core_makePermalink($ciniki, $section['label']);
        $blocks[] = $section['settings'];
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
