<?php
//
// Description
// -----------
// This method will add a new section for the tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to add the Section to.
//
// Returns
// -------
//
function ciniki_wng_sectionAdd(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'site_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Site'),
        'page_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Page'),
        'sequence'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Order'),
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Options'),
        'ref'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Section'),
        'label'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Name'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'checkAccess');
    $rc = ciniki_wng_checkAccess($ciniki, $args['tnid'], 'ciniki.wng.sectionAdd');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Check if header or footer 
    //
    if( !isset($args['flags']) ) {
        $args['flags'] = 0;
    }
    if( $args['page_id'] == 'header' || $args['page_id'] == 'footer' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'siteLoad');
        $rc = ciniki_wng_siteLoad($ciniki, $args['tnid'], $args['site_id']);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.62', 'msg'=>'', 'err'=>$rc['err']));
        }
        if( $args['page_id'] == 'header' ) {
            $args['flags'] |= 0x01;
        } elseif( $args['page_id'] == 'footer' ) {
            $args['flags'] |= 0x02;
        }
        $args['page_id'] = $rc['site']['homepage_id'];
    }

    //
    // Load the section settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'sectionsAvailable');
    $rc = ciniki_wng_sectionsAvailable($ciniki, $args['tnid'], $args['site_id']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.36', 'msg'=>'Unable to load section list', 'err'=>$rc['err']));
    }
    $availablesections = isset($rc['sections']) ? $rc['sections'] : array();

    if( !isset($availablesections[$args['ref']]) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.38', 'msg'=>'Invalid section'));
    }
    $section_object = $availablesections[$args['ref']];

    //
    // Update any settings
    //
    $settings = array();
    if( isset($section_object['settings']) ) {
        foreach($section_object['settings'] as $key => $setting) {
            if( isset($ciniki['request']['args'][$key]) ) {
                $settings[$key] = $ciniki['request']['args'][$key];
            }
        }
    }

    //
    // serialize the array
    //
    $args['settings'] = serialize($settings);

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
    // Add the section to the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.wng.section', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wng');
        return $rc;
    }
    $section_id = $rc['id'];

    //
    // Update the section sequences
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'sectionSequencesUpdate');
    $rc = ciniki_wng_sectionSequencesUpdate($ciniki, $args['tnid'], $args['site_id'], $args['page_id'], ($args['flags']&0x03), $section_id, $args['sequence']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.47', 'msg'=>'Unable to move section', 'err'=>$rc['err']));
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
    ciniki_core_hookExec($ciniki, $args['tnid'], 'ciniki', 'web', 'indexObject', array('object'=>'ciniki.wng.section', 'object_id'=>$section_id));

    return array('stat'=>'ok', 'id'=>$section_id);
}
?>
