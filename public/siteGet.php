<?php
//
// Description
// ===========
// This method will return all the information about an site.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the site is attached to.
// site_id:          The ID of the site to get the details for.
//
// Returns
// -------
//
function ciniki_wng_siteGet($ciniki) {
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
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'checkAccess');
    $rc = ciniki_wng_checkAccess($ciniki, $args['tnid'], 'ciniki.wng.siteGet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'php');

    //
    // Return default for new Site
    //
    if( $args['site_id'] == 0 ) {
        $site = array('id'=>0,
            'name'=>'',
            'status'=>'10',
            'domain_id'=>'',
            'permalink'=>'',
            'flags'=>'0',
            'theme'=>'',
            'lang'=>'en',
            'css_classes'=>'',
        );
    }

    //
    // Get the details for an existing Site
    //
    else {
        $strsql = "SELECT ciniki_wng_sites.id, "
            . "ciniki_wng_sites.name, "
            . "ciniki_wng_sites.status, "
            . "ciniki_wng_sites.domain_id, "
            . "ciniki_wng_sites.permalink, "
            . "ciniki_wng_sites.flags, "
            . "ciniki_wng_sites.theme, "
            . "ciniki_wng_sites.lang, "
            . "ciniki_wng_sites.css_classes "
            . "FROM ciniki_wng_sites "
            . "WHERE ciniki_wng_sites.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_wng_sites.id = '" . ciniki_core_dbQuote($ciniki, $args['site_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wng', array(
            array('container'=>'sites', 'fname'=>'id', 
                'fields'=>array('name', 'status', 'domain_id', 'permalink', 'flags', 'theme', 'lang', 'css_classes'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.115', 'msg'=>'Site not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['sites'][0]) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.116', 'msg'=>'Unable to find Site'));
        }
        $site = $rc['sites'][0];
    }

    $strsql = "SELECT id, domain "
        . "FROM ciniki_tenant_domains "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wng', array(
        array('container'=>'domains', 'fname'=>'id', 'fields'=>array('id', 'domain')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.16', 'msg'=>'Unable to load domains', 'err'=>$rc['err']));
    }
    $domains = isset($rc['domains']) ? $rc['domains'] : array();
    array_unshift($domains, array('id'=>0, 'domain'=>'Master Domain'));

    return array('stat'=>'ok', 'site'=>$site, 'domains'=>$domains);
}
?>
