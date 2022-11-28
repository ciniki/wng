<?php
//
// Description
// -----------
// This function will update the indexes for all the WNG sites for a customer
//
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_sitesIndexUpdate(&$ciniki, $tnid) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'siteIndexUpdate');

    error_log('indexing sites');
    //
    // Get the lists of sites
    //
    $strsql = "SELECT id "
        . "FROM ciniki_wng_sites "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND status <= 10 "   // Active or draft
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wng', array(
        array('container'=>'sites', 'fname'=>'id', 'fields'=>array('id')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.222', 'msg'=>'Unable to load sites', 'err'=>$rc['err']));
    }
    $sites = isset($rc['sites']) ? $rc['sites'] : array();

    //
    // Run the update for each site
    //
    foreach($sites as $site) {
        $rc = ciniki_wng_siteIndexUpdate($ciniki, $tnid, array(
            'site_id' => $site['id'],
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.223', 'msg'=>'Unable to index site', 'err'=>$rc['err']));
        }
    }

    return array('stat'=>'ok');
}
?>
