<?php
//
// Description
// -----------
// Process the section that will display a video and paragraph.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_processors_contentvideo(&$ciniki, $tnid, &$request, $section) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();

    $section['settings']['type'] = 'contentvideo';
    $section['settings']['sequence'] = $section['sequence'];
    $section['settings']['class'] = 'section-' . ciniki_core_makePermalink($ciniki, $section['label']);
    $blocks[] = $section['settings'];

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
