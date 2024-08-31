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
// page_id:          The ID of the page to get the details for.
//
// Returns
// -------
//
function ciniki_wng_pageGet($ciniki) {
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
    $rc = ciniki_wng_checkAccess($ciniki, $args['tnid'], 'ciniki.wng.pageGet');
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

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'php');

    //
    // Return default for new Page
    //
    if( $args['page_id'] == 0 ) {
        $page = array('id'=>0,
            'site_id'=>'',
            'parent_id'=>'',
            'ptype'=>'10',
            'sequence'=>'1',
            'title'=>'',
            'page_title'=>'',
            'permalink'=>'',
            'path'=>'',
            'menu_flags'=>'1',
            'flags'=>'0',
            'password'=>'',
            'redirect_url'=>'',
            'image_id'=>'',
            'image_caption'=>'',
            'synopsis'=>'',
            'meta_description'=>'',
        );
    }

    //
    // Get the details for an existing Page
    //
    else {
        $strsql = "SELECT ciniki_wng_pages.id, "
            . "ciniki_wng_pages.site_id, "
            . "ciniki_wng_pages.parent_id, "
            . "ciniki_wng_pages.ptype, "
            . "ciniki_wng_pages.sequence, "
            . "ciniki_wng_pages.title, "
            . "ciniki_wng_pages.page_title, "
            . "ciniki_wng_pages.permalink, "
            . "ciniki_wng_pages.path, "
            . "ciniki_wng_pages.menu_flags, "
            . "ciniki_wng_pages.flags, "
            . "ciniki_wng_pages.password, "
            . "ciniki_wng_pages.redirect_url, "
            . "ciniki_wng_pages.image_id, "
            . "ciniki_wng_pages.image_caption, "
            . "ciniki_wng_pages.synopsis, "
            . "ciniki_wng_pages.meta_description "
            . "FROM ciniki_wng_pages "
            . "WHERE ciniki_wng_pages.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_wng_pages.id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wng', array(
            array('container'=>'pages', 'fname'=>'id', 
                'fields'=>array('site_id', 'parent_id', 'ptype', 'sequence', 'title', 'page_title', 'permalink', 'path', 'menu_flags', 'flags', 'password', 'redirect_url', 'image_id', 'image_caption', 'synopsis', 'meta_description'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.128', 'msg'=>'Page not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['pages'][0]) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.129', 'msg'=>'Unable to find Page'));
        }
        $page = $rc['pages'][0];
    }

    return array('stat'=>'ok', 'page'=>$page);
}
?>
