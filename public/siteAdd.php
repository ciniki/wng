<?php
//
// Description
// -----------
// This method will add a new site for the tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to add the Site to.
//
// Returns
// -------
//
function ciniki_wng_siteAdd(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'name'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Name'),
        'status'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Status'),
        'domain_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Domain'),
        'permalink'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Permalink'),
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Options'),
        'theme'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Theme'),
        'lang'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Language'),
        'css_classes'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'CSS Classes'),
        'duplicate_site_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Duplicate Site'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    if( isset($args['permalink']) ) {
        $args['permalink'] = strtolower($args['permalink']);
    }

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'checkAccess');
    $rc = ciniki_wng_checkAccess($ciniki, $args['tnid'], 'ciniki.wng.siteAdd');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Setup permalink
    //
    if( !isset($args['permalink']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
        $args['permalink'] = ciniki_core_makePermalink($ciniki, $args['name']);
    }

    //
    // Make sure the permalink is unique
    //
    $strsql = "SELECT id, name, permalink "
        . "FROM ciniki_wng_sites "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'item');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( $rc['num_rows'] > 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.111', 'msg'=>'You already have a site with that name, please choose another.'));
    }

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.wng');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Add the site to the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.wng.site', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wng');
        return $rc;
    }
    $site_id = $rc['id'];

    //
    // Check if duplicate 
    // **NOTE:** Incomplete code, issue is all references to page ids within sections
    // would need to be updated. Complex process.
    //
    if( isset($args['duplicate_site_id']) && $args['duplicate_site_id'] > 0 ) {
        //
        // Select settings
        //
        $strsql = "SELECT id, detail_key, detail_value "
            . "FROM ciniki_wng_settings "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND site_id = '" . ciniki_core_dbQuote($ciniki, $args['site_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'site');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $settings = isset($rc['rows']) ? $rc['rows'] : array();

        foreach($settings as $setting) {
            $setting['site_id'] = $site_id;
            $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.wng.setting', $setting, 0x04);
            if( $rc['stat'] != 'ok' ) {
                ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wng');
                return $rc;
            }
            $setting_id = $rc['id'];
        }

        //
        // Select pages and sections
        //

    }
    else {
        //
        // Add the home page for the site
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
        $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.wng.page', array(
            'site_id' => $site_id,
            'parent_id' => 0,
            'sequence' => 1,
            'title' => 'Home',
            'permalink' => 'home',
            'path' => '/',
            'flags' => 0,
            'password' => '',
            'image_id' => 0,
            'image_caption' => '',
            'synopsis' => '',
            ), 0x04);
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wng');
            return $rc;
        }
        $page_id = $rc['id'];
    }

    //
    // Commit the transaction
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.wng');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'ciniki', 'wng');

    //
    // Update the web index if enabled
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'hookExec');
    ciniki_core_hookExec($ciniki, $args['tnid'], 'ciniki', 'web', 'indexObject', array('object'=>'ciniki.wng.site', 'object_id'=>$site_id));

    return array('stat'=>'ok', 'id'=>$site_id);
}
?>
