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
function ciniki_wng_processors_childpages(&$ciniki, $tnid, &$request, $section) {

    $blocks = array();

    $blocks[] = array('type'=>'content', 'content'=>'process Child Pages');

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
