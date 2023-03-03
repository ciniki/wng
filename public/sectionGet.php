<?php
//
// Description
// ===========
// This method will return all the information about an section.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the section is attached to.
// section_id:          The ID of the section to get the details for.
//
// Returns
// -------
//
function ciniki_wng_sectionGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'site_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Site'),
        'page_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Site'),
        'section_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Section'),
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
    $rc = ciniki_wng_checkAccess($ciniki, $args['tnid'], 'ciniki.wng.sectionGet');
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
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'php');

    //
    // Return default for new Section
    //
    if( $args['section_id'] == 0 ) {
        //
        // Get the next sequence number
        //
        $strsql = "SELECT MAX(sequence) AS num "
            . "FROM ciniki_wng_sections "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND site_id = '" . ciniki_core_dbQuote($ciniki, $args['site_id']) . "' "
            . "AND page_id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng','item');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $seq = (isset($rc['item']['num']) ? $rc['item']['num'] + 1 : 1);
        
        $section = array('id'=>0,
            'site_id' => $args['site_id'],
            'page_id' => $args['page_id'],
            'ref' => '',
            'sequence' => $seq,
            'flags' => '0',
            'label' => '',
            'settings' => array(),
        );
    }

    //
    // Get the details for an existing Section
    //
    else {
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
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wng', array(
            array('container'=>'sections', 'fname'=>'id', 
                'fields'=>array('site_id', 'page_id', 'sequence', 'flags', 'ref', 'label', 'settings'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.134', 'msg'=>'Section not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['sections'][0]) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.135', 'msg'=>'Unable to find Section'));
        }
        $section = $rc['sections'][0];
        $section['settings'] = unserialize($section['settings']);
    }

    //
    // Get the list of sections available
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'sectionsAvailable');
    $rc = ciniki_wng_sectionsAvailable($ciniki, $args['tnid'], $args['site_id']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.48', 'msg'=>'Unable to load section list', 'err'=>$rc['err']));
    }
    $sections = isset($rc['sections']) ? $rc['sections'] : array();

    //
    // Check if section has repeats
    //
    if( isset($sections[$section['ref']]['repeats']) ) {
        $repeats = $sections[$section['ref']]['repeats'];
        $section['repeats'] = array();
        for($i = 1; $i <= 100; $i++) {
            $repeat = array();
            foreach($repeats['fields'] as $fid => $field) {
                if( isset($section['settings']["{$fid}-{$i}"]) ) {
                    $repeat[$fid] = $section['settings']["{$fid}-{$i}"];
                }
/*                if( isset($field['pages']) && $field['pages'] == 'yes' && isset($section['settings']["{$fid}-{$i}"]) ) {
                    if( $section['settings']["{$fid}-{$i}"] > 0 ) {
                        error_log('page');
                    }
                    
                } */
            }
            if( count($repeat) > 0 ) {
                $section['repeats'][$i] = $repeat;
            }
        }
    }

    return array('stat'=>'ok', 'section'=>$section, 'availablesections'=>$sections);
}
?>
