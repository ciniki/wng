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
function ciniki_wng_sectionUpdate(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'section_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Section'),
        'site_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Site'),
        'page_id'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Page'),
        'sequence'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Order'),
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Options'),
        'ref'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Section'),
        'label'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Name'),
        'delete_repeat'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Name'),
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
    $rc = ciniki_wng_checkAccess($ciniki, $args['tnid'], 'ciniki.wng.sectionUpdate');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load the section
    //
    $strsql = "SELECT ciniki_wng_sections.id, "
        . "ciniki_wng_sections.site_id, "
        . "ciniki_wng_sections.page_id, "
        . "ciniki_wng_sections.ref, "
        . "ciniki_wng_sections.sequence, "
        . "ciniki_wng_sections.flags, "
        . "ciniki_wng_sections.label, "
        . "ciniki_wng_sections.settings "
        . "FROM ciniki_wng_sections "
        . "WHERE ciniki_wng_sections.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ciniki_wng_sections.id = '" . ciniki_core_dbQuote($ciniki, $args['section_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'section');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.39', 'msg'=>'Unable to load section', 'err'=>$rc['err']));
    }
    if( !isset($rc['section']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.40', 'msg'=>'Unable to find requested section'));
    }
    $section = $rc['section'];
    if( isset($section['settings'][0]) && $section['settings'][0] == '{' ) {
        $settings = json_decode($section['settings'], true);
    } elseif( isset($section['settings']) && $section['settings'] != '' ) {
        $section['settings'] = str_replace("\xC3\xA2\xE2\x82\xAC\xE2\x80\x9C", '-', $section['settings']);
        $section['settings'] = utf8_decode($section['settings']);
        $section['settings'] = preg_replace_callback('!s:(\d+):"(.*?)";!s', function($m) {
            return 's:' . strlen($m[2]) . ':"' . $m[2] . '";';
            }, $section['settings']);
        $settings = unserialize($section['settings']);
    } else {
        $settings = array();
    }

    //
    // Load the section settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'sectionsAvailable');
    $rc = ciniki_wng_sectionsAvailable($ciniki, $args['tnid'], $args['site_id']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.51', 'msg'=>'Unable to load section list', 'err'=>$rc['err']));
    }
    $availablesections = isset($rc['sections']) ? $rc['sections'] : array();

    if( isset($args['ref']) ) {
        if( !isset($availablesections[$args['ref']]) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.52', 'msg'=>'Invalid section'));
        }
        $section_object = $availablesections[$args['ref']];
    } else {
        if( !isset($availablesections[$section['ref']]) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.53', 'msg'=>'Section is no longer valid and must be removed.'));
        }
        $section_object = $availablesections[$section['ref']];
    }

    //
    // Update any settings
    //
    if( isset($section_object['settings']) ) {
        foreach($section_object['settings'] as $key => $setting) {
            if( isset($ciniki['request']['args'][$key]) ) {
                $settings[$key] = trim($ciniki['request']['args'][$key]);
            }
        }
    }

    //
    // Check for any repeats
    //
    if( isset($section_object['repeats']['fields']) ) {
        for($i = 1; $i <= 100; $i++) {

            foreach($section_object['repeats']['fields'] as $key => $setting) {
                if( isset($args['delete_repeat']) && $args['delete_repeat'] == $i ) {
                    if( isset($settings["{$key}-{$i}"]) ) {
                        unset($settings["{$key}-{$i}"]);
                    }
                }
                elseif( isset($args['delete_repeat']) && $args['delete_repeat'] < $i ) {
                    //
                    // Shift everything down 1
                    //
                    $prev = ($i-1);
                    if( isset($settings["{$key}-{$i}"]) ) {
                        $settings["{$key}-{$prev}"] = $settings["{$key}-{$i}"];
                        unset($settings["{$key}-{$i}"]);
                    }
                }
                elseif( isset($ciniki['request']['args']["{$key}-{$i}"]) ) {
                    $settings["{$key}-{$i}"] = trim($ciniki['request']['args']["{$key}-{$i}"]);
                }
            }
        }
    }

    //
    // Reserialize the array
    //
    $settings = json_encode($settings);
    if( $settings != $section['settings'] ) {
        $args['settings'] = $settings;
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
    // Check if header or footer 
    //
    $flags = 0;
    if( isset($args['page_id']) && ($args['page_id'] == 'header' || $args['page_id'] == 'footer') ) {
        if( $args['page_id'] == 'header' ) {
            $flags |= 0x01;
        } elseif( $args['page_id'] == 'footer' ) {
            $flags |= 0x02;
        }
        unset($args['page_id']);
    }

    //
    // Update the Section in the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $rc = ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.wng.section', $args['section_id'], $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.wng');
        return $rc;
    }

    //
    // Update the section sequences
    //
    if( isset($args['sequence']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'sectionSequencesUpdate');
        $rc = ciniki_wng_sectionSequencesUpdate($ciniki, $args['tnid'], $section['site_id'], $section['page_id'], $flags, $args['section_id'], $args['sequence']);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.49', 'msg'=>'Unable to move section', 'err'=>$rc['err']));
        }
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
    ciniki_core_hookExec($ciniki, $args['tnid'], 'ciniki', 'web', 'indexObject', array('object'=>'ciniki.wng.section', 'object_id'=>$args['section_id']));
    ciniki_core_hookExec($ciniki, $args['tnid'], 'ciniki', 'wng', 'indexObject', array());

    return array('stat'=>'ok');
}
?>
