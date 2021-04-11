<?php
//
// Description
// -----------
// This method will rebuild all the cached entries, and other settings for a site such as page paths.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_siteRebuild(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'site_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Site'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'checkAccess');
    $rc = ciniki_wng_checkAccess($ciniki, $args['tnid'], 'ciniki.wng.siteAdd');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Rebuild all the paths
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'pathsUpdate');
    $rc = ciniki_wng_pathsUpdate($ciniki, $args['tnid'], $args['site_id']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Rebuild the theme cache
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheRebuild');
    $rc = ciniki_wng_cacheRebuild($ciniki, $args['tnid'], $args['site_id']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }


    return array('stat'=>'ok');
}
?>
