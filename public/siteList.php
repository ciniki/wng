<?php
//
// Description
// -----------
// This method will return the list of Sites for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Site for.
//
// Returns
// -------
//
function ciniki_wng_siteList($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'checkAccess');
    $rc = ciniki_wng_checkAccess($ciniki, $args['tnid'], 'ciniki.wng.siteList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load maps
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'maps');
    $rc = ciniki_wng_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps'];

    //
    // Get the list of sites
    //
    $strsql = "SELECT sites.id, "
        . "sites.name, "
        . "sites.status, "
        . "sites.status AS status_text, "
        . "sites.domain_id, "
        . "domains.domain, "
        . "sites.permalink, "
        . "sites.flags "
        . "FROM ciniki_wng_sites AS sites "
        . "LEFT JOIN ciniki_tenant_domains AS domains ON ( "
            . "sites.domain_id = domains.id "
            . "AND domains.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "WHERE sites.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wng', array(
        array('container'=>'sites', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'status', 'status_text', 'domain_id', 'domain', 'permalink', 'flags'),
            'maps'=>array('status_text'=>$maps['site']['status']),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $sites = isset($rc['sites']) ? $rc['sites'] : array();

    return array('stat'=>'ok', 'sites'=>$sites);
}
?>
