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
function ciniki_wng_cacheClear(&$ciniki) {
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
    // Load the site
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'siteLoad');
    $rc = ciniki_wng_siteLoad($ciniki, $args['tnid'], $args['site_id'], 'yes');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.83', 'msg'=>'Unable to load site', 'err'=>$rc['err']));
    }
    $site = isset($rc['site']) ? $rc['site'] : array();

    //
    // Make sure required directories are created
    //
    if( !is_dir($site['cache_dir']) ) {
        if( !mkdir($site['cache_dir'], 0755, true) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.84', 'msg'=>'Unable to create cache dir', 'err'=>$rc['err']));
        }
    }

    //
    // Clear the cache directory
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'recursiveRmdir');
    $rc = ciniki_core_recursiveRmdir($ciniki, $site['cache_dir'], array());
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.147', 'msg'=>'Unable to clear cache', 'err'=>$rc['err']));
    }

    if( !is_dir($site['cache_dir'] . '/images') ) {
        if( !mkdir($site['cache_dir'] . '/images', 0755, true) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.86', 'msg'=>'Unable to create cache/images dir', 'err'=>$rc['err']));
        }
    }
    
    //
    // Rebuild all the paths
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheRebuild');
    $rc = ciniki_wng_cacheRebuild($ciniki, $args['tnid'], $site);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    return array('stat'=>'ok');
}
?>
