<?php
//
// Description
// -----------
// The API call to update the index for a site.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_siteIndexRefresh(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'site_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Site'),
        'clear'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Clear'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'checkAccess');
    $rc = ciniki_wng_checkAccess($ciniki, $args['tnid'], 'ciniki.wng.siteIndexRefresh');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Rebuild the index
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'siteIndexUpdate');
    $rc = ciniki_wng_siteIndexUpdate($ciniki, $args['tnid'], $args);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    return array('stat'=>'ok');
}
?>
