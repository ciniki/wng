<?php
//
// Description
// -----------
// This function will call all the functions to rebuild a site's cache
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_cacheRebuild(&$ciniki, $tnid, $site_id) {

    //
    // Load the site
    //
    if( is_array($site_id) ) {
        $site = $site_id;
    } else {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'siteLoad');
        $rc = ciniki_wng_siteLoad($ciniki, $tnid, $site_id, 'yes');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.175', 'msg'=>'Unable to load site', 'err'=>$rc['err']));
        }
        $site = isset($rc['site']) ? $rc['site'] : array();
    }

    //
    // Make sure required directories are created
    //
    if( !is_dir($site['cache_dir']) ) {
        if( !mkdir($site['cache_dir'], 0755, true) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.172', 'msg'=>'Unable to create cache dir', 'err'=>$rc['err']));
        }
    }
    if( !is_dir($site['cache_dir'] . '/images') ) {
        if( !mkdir($site['cache_dir'] . '/images', 0755, true) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.173', 'msg'=>'Unable to create cache/images dir', 'err'=>$rc['err']));
        }
    }

    //
    // Rebuild the theme cache
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheThemeUpdate');
    $rc = ciniki_wng_cacheThemeUpdate($ciniki, $tnid, $site);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    return array('stat'=>'ok');
}
?>
