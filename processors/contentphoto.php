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

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();

    $section['settings']['type'] = 'contentphoto';
    $section['settings']['sequence'] = $section['sequence'];
    $section['settings']['title_sequence'] = isset($section['title_sequence']) ? $section['title_sequence'] : 2;
    $section['settings']['class'] = 'section-' . ciniki_core_makePermalink($ciniki, $section['label']);
    $blocks[] = $section['settings'];

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
