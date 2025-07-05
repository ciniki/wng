<?php
//
// Description
// ===========
// This method will return all the information about an page.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the page is attached to.
// page_id:          The ID of the page to duplicate the details for.
//
// Returns
// -------
//
function ciniki_wng_pageCopy($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'page_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Page'),
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
    $rc = ciniki_wng_checkAccess($ciniki, $args['tnid'], 'ciniki.wng.pageCopy');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load the existing page
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'pageLoad');
    $rc = ciniki_wng_pageLoad($ciniki, $args['tnid'], [], $args['page_id']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $page = $rc['page'];
    $page['title'] .= ' Copy';

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
    $page['permalink'] = ciniki_core_makePermalink($ciniki, $page['title']);
    $page['path'] = ''; // Will be updated by pathsUpdate

    //
    // Validate the permalink to make sure it is unique and allowed
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'pagePermalinkValidate');
    $rc = ciniki_wng_pagePermalinkValidate($ciniki, $args['tnid'], $page);
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
    $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.wng.page', $page, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wng');
        return $rc;
    }
    $page_id = $rc['id'];

    //
    // Update the page paths
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'pathsUpdate');
    $rc = ciniki_wng_pathsUpdate($ciniki, $args['tnid'], $page['site_id']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.24', 'msg'=>'Unable to update page paths', 'err'=>$rc['err']));
    }

    //
    // Add the page content
    //
    foreach($page['sections'] as $section) {
        //
        // Add the section to the database
        //
        $section['site_id'] = $page['site_id'];
        $section['page_id'] = $page_id;
        $section['settings'] = json_encode($section['settings']);
        $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.wng.section', $section, 0x04);
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wng');
            return $rc;
        }
        $section_id = $rc['id'];
    }

    //
    // Commit the transaction
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.wng');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    return array('stat'=>'ok', 'id'=>$page_id);
}
?>
