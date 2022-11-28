<?php
//
// Description
// -----------
// This method will add a new page for the tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to add the Page to.
//
// Returns
// -------
//
function ciniki_wng_pageAdd(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'site_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Site'),
        'parent_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Parent'),
        'ptype'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Page Type'),
        'sequence'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Order'),
        'title'=>array('required'=>'yes', 'blank'=>'no', 'trim'=>'yes', 'name'=>'Title'),
        'page_title'=>array('required'=>'no', 'blank'=>'yes', 'trim'=>'yes', 'name'=>'Page Title'),
        'menu_flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Menu Options'),
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Options'),
        'password'=>array('required'=>'no', 'blank'=>'yes', 'trim'=>'yes', 'name'=>'Password'),
        'redirect_url'=>array('required'=>'no', 'blank'=>'yes', 'trim'=>'yes', 'name'=>'Redirect URL'),
        'image_id'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Image'),
        'image_caption'=>array('required'=>'no', 'blank'=>'yes', 'trim'=>'yes', 'name'=>'Image Caption'),
        'synopsis'=>array('required'=>'no', 'blank'=>'yes', 'trim'=>'yes', 'name'=>'Synopsis'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    // Set default path, will be updated the pathsUpdate
    $args['path'] = '';

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'checkAccess');
    $rc = ciniki_wng_checkAccess($ciniki, $args['tnid'], 'ciniki.wng.pageAdd');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Setup permalink
    //
    if( !isset($args['permalink']) || $args['permalink'] == '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
        $args['permalink'] = ciniki_core_makePermalink($ciniki, $args['title']);
    }

    //
    // Validate the permalink to make sure it is unique and allowed
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'pagePermalinkValidate');
    $rc = ciniki_wng_pagePermalinkValidate($ciniki, $args['tnid'], $args);
    if( $rc['stat'] == 'warn' ) {
        return $rc;
    }
    elseif( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.176', 'msg'=>'Unable to validate page title', 'err'=>$rc['err']));
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
    // Add the page to the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.wng.page', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wng');
        return $rc;
    }
    $page_id = $rc['id'];

    //
    // Update the page paths
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'pathsUpdate');
    $rc = ciniki_wng_pathsUpdate($ciniki, $args['tnid'], $args['site_id']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.24', 'msg'=>'Unable to update page paths', 'err'=>$rc['err']));
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
    ciniki_core_hookExec($ciniki, $args['tnid'], 'ciniki', 'web', 'indexObject', array('object'=>'ciniki.wng.page', 'object_id'=>$page_id));
    ciniki_core_hookExec($ciniki, $args['tnid'], 'ciniki', 'wng', 'indexObject', array());

    return array('stat'=>'ok', 'id'=>$page_id);
}
?>
