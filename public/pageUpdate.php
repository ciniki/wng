<?php
//
// Description
// ===========
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_wng_pageUpdate(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'page_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Page'),
        'site_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Site'),
        'parent_id'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Parent'),
        'ptype'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Page Type'),
        'sequence'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Order'),
        'title'=>array('required'=>'no', 'blank'=>'no', 'trim'=>'yes', 'name'=>'Title'),
        'page_title'=>array('required'=>'no', 'blank'=>'yes', 'trim'=>'yes', 'name'=>'Page Title'),
        'permalink'=>array('required'=>'no', 'blank'=>'no', 'trim'=>'yes', 'name'=>'Permalink'),
        'path'=>array('required'=>'no', 'blank'=>'no', 'trim'=>'yes', 'name'=>'path'),
        'menu_flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Menu Options'),
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Options'),
        'password'=>array('required'=>'no', 'blank'=>'yes', 'trim'=>'yes', 'name'=>'Password'),
        'redirect_url'=>array('required'=>'no', 'blank'=>'yes', 'trim'=>'yes', 'name'=>'Redirect URL'),
        'image_id'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Image'),
        'image_caption'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image Caption'),
        'synopsis'=>array('required'=>'no', 'blank'=>'yes', 'trim'=>'yes', 'name'=>'Synopsis'),
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
    $rc = ciniki_wng_checkAccess($ciniki, $args['tnid'], 'ciniki.wng.pageUpdate');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load the current page details
    //
    $strsql = "SELECT ciniki_wng_pages.id, "
        . "ciniki_wng_pages.parent_id, "
        . "ciniki_wng_pages.site_id "
        . "FROM ciniki_wng_pages "
        . "WHERE ciniki_wng_pages.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ciniki_wng_pages.id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'page');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.31', 'msg'=>'Unable to load page', 'err'=>$rc['err']));
    }
    if( !isset($rc['page']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.32', 'msg'=>'Unable to find requested page'));
    }
    $page = $rc['page'];

    //
    // Check if title is changing
    //
    if( isset($args['title']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
        $args['permalink'] = ciniki_core_makePermalink($ciniki, $args['title']);

        if( !isset($args['parent_id']) ) {
            $args['parent_id'] = $page['parent_id'];
        }
        if( !isset($args['site_id']) ) {
            $args['site_id'] = $page['site_id'];
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
             return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.130', 'msg'=>'You already have an page with this title, please choose another.'));
        }
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
    // Update the Page in the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $rc = ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.wng.page', $args['page_id'], $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wng');
        return $rc;
    }

    //
    // Update the page paths
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'pathsUpdate');
    $rc = ciniki_wng_pathsUpdate($ciniki, $args['tnid'], $page['site_id']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.54', 'msg'=>'Unable to update page paths', 'err'=>$rc['err']));
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
    ciniki_core_hookExec($ciniki, $args['tnid'], 'ciniki', 'web', 'indexObject', array('object'=>'ciniki.wng.page', 'object_id'=>$args['page_id']));

    return array('stat'=>'ok');
}
?>
