<?php
//
// Description
// -----------
// This method will return the list of Sections for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Section for.
//
// Returns
// -------
//
function ciniki_wng_sectionList($ciniki) {
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
    $rc = ciniki_wng_checkAccess($ciniki, $args['tnid'], 'ciniki.wng.sectionList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of sections
    //
    $strsql = "SELECT ciniki_wng_sections.id, "
        . "ciniki_wng_sections.site_id, "
        . "ciniki_wng_sections.page_id, "
        . "ciniki_wng_sections.sequence, "
        . "ciniki_wng_sections.flags, "
        . "ciniki_wng_sections.ref, "
        . "ciniki_wng_sections.label "
        . "FROM ciniki_wng_sections "
        . "WHERE ciniki_wng_sections.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wng', array(
        array('container'=>'sections', 'fname'=>'id', 
            'fields'=>array('id', 'site_id', 'page_id', 'sequence', 'flags', 'ref', 'label')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['sections']) ) {
        $sections = $rc['sections'];
        $section_ids = array();
        foreach($sections as $iid => $section) {
            $section_ids[] = $section['id'];
        }
    } else {
        $sections = array();
        $section_ids = array();
    }

    return array('stat'=>'ok', 'sections'=>$sections, 'nplist'=>$section_ids);
}
?>
