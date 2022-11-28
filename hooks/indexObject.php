<?php
//
// Description
// -----------
// This function will add/modify/delete an object in the web index.
//
// Arguments
// ---------
// ciniki:
//
// Returns
// -------
//
function ciniki_wng_hooks_indexObject(&$ciniki, $tnid, $args) {
  
    //
    // Setup the wng indexer so it runs after return to user
    //
    if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.wng', 0x4000) ) {
        $ciniki['wngindexer'] = 'yes';
    }

    return array('stat'=>'ok');
}
?>
