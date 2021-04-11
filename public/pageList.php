<?php
//
// Description
// -----------
// This method will return the list of Pages for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Page for.
//
// Returns
// -------
//
function ciniki_wng_pageList($ciniki) {
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
    $rc = ciniki_wng_checkAccess($ciniki, $args['tnid'], 'ciniki.wng.pageList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of pages
    //
    $strsql = "SELECT ciniki_wng_pages.id, "
        . "ciniki_wng_pages.site_id, "
        . "ciniki_wng_pages.parent_id, "
        . "ciniki_wng_pages.ptype, "
        . "ciniki_wng_pages.sequence, "
        . "ciniki_wng_pages.title, "
        . "ciniki_wng_pages.page_title, "
        . "ciniki_wng_pages.permalink, "
        . "ciniki_wng_pages.menu_flags, "
        . "ciniki_wng_pages.flags, "
        . "ciniki_wng_pages.password, "
        . "ciniki_wng_pages.redirect_url, "
        . "ciniki_wng_pages.image_caption "
        . "FROM ciniki_wng_pages "
        . "WHERE ciniki_wng_pages.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wng', array(
        array('container'=>'pages', 'fname'=>'id', 
            'fields'=>array('id', 'site_id', 'parent_id', 'ptype', 'sequence', 'title', 'page_title', 'permalink', 'menu_flags', 'flags', 'password', 'redirect_url', 'image_caption')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['pages']) ) {
        $pages = $rc['pages'];
        $page_ids = array();
        foreach($pages as $iid => $page) {
            $page_ids[] = $page['id'];
        }
    } else {
        $pages = array();
        $page_ids = array();
    }

    return array('stat'=>'ok', 'pages'=>$pages, 'nplist'=>$page_ids);
}
?>
