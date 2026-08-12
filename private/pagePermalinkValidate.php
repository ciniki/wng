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
function ciniki_wng_pagePermalinkValidate(&$ciniki, $tnid, $args) {

    //
    // Check for required variables
    //
    if( !isset($args['site_id']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.181', 'msg'=>'No website specified', 'err'=>$rc['err']));
    }
    if( !isset($args['permalink']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.205', 'msg'=>'No url specified', 'err'=>$rc['err']));
    }

    //
    // Load the Home page of the site
    //
    $strsql = "SELECT id "
        . "FROM ciniki_wng_pages "
        . "WHERE site_id = '" . ciniki_core_dbQuote($ciniki, $args['site_id']) . "' "
        . "AND parent_id = 0 "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'home');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.182', 'msg'=>'Unable to load home', 'err'=>$rc['err']));
    }
    if( !isset($rc['home']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.183', 'msg'=>'Unable to find home page'));
    }
    $home = $rc['home'];

    //
    // Check to make sure the page is not a restricted name
    //
    if( !isset($args['parent_id']) || $args['parent_id'] == 0 || $args['parent_id'] == $home['id'] ) {
        if( $args['permalink'] == 'home' 
            || $args['permalink'] == 'mail' 
            || $args['permalink'] == 'manager' 
            || $args['permalink'] == 'admin' 
            || $args['permalink'] == 'account' 
            || $args['permalink'] == 'cart' 
            || $args['permalink'] == 'search' 
            || $args['permalink'] == 'cpi' 
            ) {
            return array('stat'=>'warn', 'err'=>array('code'=>'ciniki.wng.184', 'msg'=>'That menu title is not allowed.'));
        }
    }

    //
    // Make sure the permalink is unique
    //
    $strsql = "SELECT id, title, permalink "
        . "FROM ciniki_wng_pages "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND site_id = '" . ciniki_core_dbQuote($ciniki, $args['site_id']) . "' "
        . "AND parent_id = '" . ciniki_core_dbQuote($ciniki, $args['parent_id']) . "' "
        . "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'item');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( $rc['num_rows'] > 0 ) {
        return array('stat'=>'warn', 'err'=>array('code'=>'ciniki.wng.124', 'msg'=>'You already have a page with that name, please choose another.'));
    }

    return array('stat'=>'ok');
}
?>
