<?php
//
// Description
// ===========
// This method is used by the ui/main to edit a website.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the site is attached to.
// site_id:          The ID of the site to get the details for.
//
// Returns
// -------
//
function ciniki_wng_site($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'site_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Site'),
        'view'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'View'),
        'page_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Page'),
        'action'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Action'),
        'image_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image'),
        'section_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Section'),
        'section_sequence'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'New Section Order'),
        'section_flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'New Section Placement Flags'),
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
    $rc = ciniki_wng_checkAccess($ciniki, $args['tnid'], 'ciniki.wng.site');
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
    // Check if action to be performed
    //
    if( isset($args['action']) && $args['action'] == 'sectionsequenceupdate' 
        && isset($args['page_id']) 
        && isset($args['site_id']) 
        && isset($args['section_id']) 
        && isset($args['section_sequence']) 
        && isset($args['section_flags']) 
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'sectionSequencesUpdate');
        $rc = ciniki_wng_sectionSequencesUpdate($ciniki, $args['tnid'], $args['site_id'], $args['page_id'], $args['section_flags'], $args['section_id'], $args['section_sequence']);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.41', 'msg'=>'Unable to move section', 'err'=>$rc['err']));
        }
    }
    elseif( isset($args['action']) && $args['action'] == 'addthemeimage' 
        && isset($_FILES['uploadfile']['tmp_name']) 
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'insertFromUpload');
        $rc = ciniki_images_insertFromUpload($ciniki, $args['tnid'], $ciniki['session']['user']['id'], $_FILES['uploadfile'], 1, '', '', 'yes');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.58', 'msg'=>'Unable to add image', 'err'=>$rc['err']));
        }
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'themeImageAdd');
        $rc = ciniki_wng_themeImageAdd($ciniki, $args['tnid'], $args['site_id'], $rc['id']);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.70', 'msg'=>'Unable to save image', 'err'=>$rc['err']));
        }
    }
    elseif( isset($args['action']) && $args['action'] == 'removethemeimage' && isset($args['image_id']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'themeImageRemove');
        $rc = ciniki_wng_themeImageRemove($ciniki, $args['tnid'], $args['site_id'], $args['image_id']);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.71', 'msg'=>'Unable to save image', 'err'=>$rc['err']));
        }
    }

    //
    // Load the site
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'siteLoad');
    $rc = ciniki_wng_siteLoad($ciniki, $args['tnid'], $args['site_id'], 'yes');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.17', 'msg'=>'Unable to load the site', 'err'=>$rc['err']));
    }
    $site = $rc['site'];

    $rsp = array('stat'=>'ok', 'site'=>$site, 'headerpages'=>array());

    //
    // The breadcrumbs are used to determine where in the sitemap/tree we 
    // are at and only show sub pages for that item.
    //
    function buildBreadcrumbs($breadcrumbs, $page_id, $sitepages) {
        array_unshift($breadcrumbs, $page_id);
        if( isset($sitepages[$page_id]['parent_id']) 
            && $sitepages[$page_id]['parent_id'] > 0 
            && $sitepages[$page_id]['path'] != '/'
            ) {
            $breadcrumbs = buildBreadcrumbs($breadcrumbs, $sitepages[$page_id]['parent_id'], $sitepages);
        }
        return $breadcrumbs;
    }
    $breadcrumbs = buildBreadcrumbs(array(), $args['page_id'], $site['pages']);

    //
    // Flatten the header and footer menu to a single list
    //
    function flattenMenu($pages, $depth, $list, $sitepages, $breadcrumbs) {
        foreach($list as $page_id) {
            $indent = '';
            for($i=0;$i<$depth;$i++) {
                $indent .= ' - ';
            }
            $pages[] = array('id' => $page_id, 'name' => $indent . $sitepages[$page_id]['title']);
            // Do not follow children of home page
            if( isset($sitepages[$page_id]['children']) 
                && $sitepages[$page_id]['parent_id'] > 0 
                && ($breadcrumbs == null || in_array($page_id, $breadcrumbs))
                ) {
                $pages = flattenMenu($pages, $depth+1, $sitepages[$page_id]['children'], $sitepages, $breadcrumbs);
            }
        }
        return $pages;
    }
    if( isset($site['headermenu']) && count($site['headermenu']) > 0 ) {
        $rsp['headerpages'] = flattenMenu(array(), 0, $site['headermenu'], $site['pages'], $breadcrumbs);
        $rsp['pagelist'] = flattenMenu(array(), 0, $site['headermenu'], $site['pages'], null);
    }
    if( isset($site['footermenu']) && count($site['footermenu']) > 0 ) {
        $rsp['footerpages'] = flattenMenu(array(), 0, $site['footermenu'], $site['pages'], $breadcrumbs);
    }
    $rsp['headersections'] = isset($site['headersections']) ? $site['headersections'] : array();
    $rsp['footersections'] = isset($site['footersections']) ? $site['footersections'] : array();

    //
    // Setup list of orphan pages
    //
    if( count($site['orphans']) > 0 ) {
        $rsp['orphanpages'] = array();
        foreach($site['orphans'] as $page_id) {
            $rsp['orphanpages'][] = array('id' => $page_id, 'name' => $site['pages'][$page_id]['title']);
            if( isset($site['pages'][$page_id]['children']) ) {
                foreach($site['pages'][$page_id]['children'] as $child_id) {
                    $rsp['orphanpages'] = flattenMenu($rsp['orphanpages'], 1, $site['pages'][$page_id]['children'], $site['pages']);
                }
            }
        }
    }
    

    //
    // If page is request
    //
    if( isset($args['view']) && $args['view'] == 'page' 
        && isset($args['page_id']) && is_numeric($args['page_id']) && $args['page_id'] > 0
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'pageLoad');
        $rc = ciniki_wng_pageLoad($ciniki, $args['tnid'], $site, $args['page_id']);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.72', 'msg'=>'Unable to load page', 'err'=>$rc['err']));
        }
        $rsp['page'] = $rc['page'];
        $rsp['pagesections'] = $rc['page']['sections'];
    }

    //
    // Load the theme images
    //
    if( isset($args['view']) && $args['view'] == 'themeimages' ) {
        //
        // Get the settings for the site
        //
        $image_ids = array();
        $strsql = "SELECT id, detail_key, detail_value "
            . "FROM ciniki_wng_settings "
            . "WHERE site_id = '" . ciniki_core_dbQuote($ciniki, $args['site_id']) . "' "
            . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND detail_key = 'theme-images' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'setting');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.77', 'msg'=>'Unable to load images', 'err'=>$rc['err']));
        }
        $setting = isset($rc['setting']) ? $rc['setting'] : array();
        if( isset($setting['detail_value']) && $setting['detail_value'] != '' ) {
            $image_ids = explode(',', $setting['detail_value']);
        }
        //
        // Build image array
        //
        if( count($image_ids) > 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'hooks', 'loadBase64Thumbnails');
            $rc = ciniki_images_hooks_loadBase64Thumbnails($ciniki, $args['tnid'], array(
                'image_ids'=>$image_ids, 
                'maxlength'=>300, 
                'padding'=>'yes',
                ));
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.73', 'msg'=>'Unable to load images', 'err'=>$rc['err']));
            }
            $rsp['themeimages'] = isset($rc['images']) ? $rc['images'] : array();
        } else {
            $rsp['themeimages'] = array();
        }
    }

    return $rsp;
}
?>
