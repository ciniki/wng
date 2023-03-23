<?php
//
// Description
// -----------
// This method will delete an page.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:            The ID of the tenant the page is attached to.
// page_id:            The ID of the page to be removed.
//
// Returns
// -------
//
function ciniki_wng_pageDelete(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'page_id'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Page'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'checkAccess');
    $rc = ciniki_wng_checkAccess($ciniki, $args['tnid'], 'ciniki.wng.pageDelete');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the current settings for the page
    //
    $strsql = "SELECT id, uuid "
        . "FROM ciniki_wng_pages "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'page');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['page']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.125', 'msg'=>'Page does not exist.'));
    }
    $page = $rc['page'];

    //
    // Check for any child pages
    //
    $strsql = "SELECT COUNT(id) AS num "
        . "FROM ciniki_wng_pages "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND parent_id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
    $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.wng', 'num');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.27', 'msg'=>'Unable to load get the number of items', 'err'=>$rc['err']));
    }
    if( $rc['num'] > 0 ) {
        return array('stat'=>'warning', 'err'=>array('code'=>'ciniki.wng.28', 'msg'=>'Remove child pages first.'));
    }

    //
    // Check if any modules are currently using this object
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectCheckUsed');
    $rc = ciniki_core_objectCheckUsed($ciniki, $args['tnid'], 'ciniki.wng.page', $args['page_id']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.29', 'msg'=>'Unable to check if the page is still being used.', 'err'=>$rc['err']));
    }
    if( $rc['used'] != 'no' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.30', 'msg'=>'The page is still in use. ' . $rc['msg']));
    }

    //
    // Load the sections part of this page
    //
    $strsql = "SELECT ciniki_wng_sections.id, "
        . "ciniki_wng_sections.page_id, "
        . "ciniki_wng_sections.uuid "
        . "FROM ciniki_wng_sections "
        . "WHERE ciniki_wng_sections.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ciniki_wng_sections.page_id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'item');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.26', 'msg'=>'Unable to load item', 'err'=>$rc['err']));
    }
    $sections = isset($rc['rows']) ? $rc['rows'] : array();

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.wng');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Remove the sections
    //
    foreach($sections as $section) {    
        $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.wng.section', $section['id'], $section['uuid'], 0x04);
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wng');
            return $rc;
        }
    }

    //
    // Remove the page
    //
    $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.wng.page', $args['page_id'], $page['uuid'], 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wng');
        return $rc;
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
    ciniki_core_hookExec($ciniki, $args['tnid'], 'ciniki', 'wng', 'indexObject', array());

    return array('stat'=>'ok');
}
?>
