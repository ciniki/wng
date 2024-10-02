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

    //
    // Load the domain
    //
    if( $site['domain_id'] > 0 ) {
        $strsql = "SELECT id, "
            . "domain, "
            . "flags "
            . "FROM ciniki_tenant_domains "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_tenant_domains.id = '" . ciniki_core_dbQuote($ciniki, $site['domain_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'domain');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.149', 'msg'=>'Unable to load domain', 'err'=>$rc['err']));
        }
        if( !isset($rc['domain']) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.150', 'msg'=>'Unable to find requested domain'));
        }
        $domain = $rc['domain'];
        $site['base_url'] = '';
        if( ($rc['domain']['flags']&0x10) == 0x10 ) {
            $site['base_url'] = 'https://';
        } else {
            $site['base_url'] = 'http://';
        }
        if( ($site['flags']&0x01) == 0x01 ) {
            $site['base_url'] .= $rc['domain']['domain'];
        } else {
            $site['base_url'] .= $rc['domain']['domain'] . '/' . $site['permalink'];
        }
    } else {
        $strsql = "SELECT sitename "
            . "FROM ciniki_tenants "
            . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'tenant');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.151', 'msg'=>'Unable to load tenant', 'err'=>$rc['err']));
        }
        if( !isset($rc['tenant']) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.152', 'msg'=>'Unable to find requested tenant'));
        }
        $tenant = $rc['tenant'];
        
        $site['base_url'] = 'https://' . $ciniki['config']['ciniki.wng']['master.domain'] . '/' . $tenant['sitename'];
    }

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
    function flattenMenu($pages, $depth, $list, $sitepages, $breadcrumbs, $skiplist=array(), $indent_str='') {
        foreach($list as $page_id) {
            $indent = '';
            for($i=0;$i<$depth;$i++) {
                $indent .= $indent_str;
                //$indent .= '<span class="faicon subdue">&#xf101;</span>&nbsp;';
                //$indent .= '<span class="faicon">&#xf141;</span>&nbsp;';
                //$indent .= '&nbsp;&nbsp;&nbsp;&nbsp;';
                //$indent .= '<span class="subdue">-&nbsp;</span>';
                //$indent .= '<span class="subdue">_&nbsp;</span>';
            }
            if( !in_array($page_id, $skiplist) ) {
                $pages[] = array(
                    'id' => $page_id, 
                    'depth' => $depth,
                    'name' => $indent . $sitepages[$page_id]['title'],
                    'name' => $indent . $sitepages[$page_id]['title'],
                    'flags' => $sitepages[$page_id]['flags'],
                    );
            }
            // Do not follow children of home page
            if( isset($sitepages[$page_id]['children']) 
                && $sitepages[$page_id]['parent_id'] > 0 
                && ($breadcrumbs == null || in_array($page_id, $breadcrumbs))
                ) {
                $pages = flattenMenu($pages, $depth+1, $sitepages[$page_id]['children'], $sitepages, $breadcrumbs, $skiplist, $indent_str);
            }
        }
        return $pages;
    }
    $skiplist = array();
    if( isset($site['headermenu']) && count($site['headermenu']) > 0 ) {
        $rsp['headerpages'] = flattenMenu(array(), 0, $site['headermenu'], $site['pages'], $breadcrumbs);
        $rsp['pagelist'] = flattenMenu(array(), 0, $site['headermenu'], $site['pages'], null, [], '-- ');
        foreach($rsp['headerpages'] as $p) {
            $skiplist[] = $p['id'];
        }
    }
    if( isset($site['footermenu']) && count($site['footermenu']) > 0 ) {
        $rsp['footerpages'] = flattenMenu(array(), 0, $site['footermenu'], $site['pages'], $breadcrumbs);
        $rsp['pagelist'] = flattenMenu($rsp['pagelist'], 0, $site['footermenu'], $site['pages'], null, $skiplist, '-- ');
        foreach($rsp['footerpages'] as $p) {
            $skiplist[] = $p['id'];
        }
    }
    $rsp['headersections'] = isset($site['headersections']) ? $site['headersections'] : array();
    $rsp['footersections'] = isset($site['footersections']) ? $site['footersections'] : array();

    //
    // Setup list of orphan pages
    //
    if( count($site['orphans']) > 0 ) {
        $rsp['orphanpages'] = flattenMenu(array(), 0, $site['orphans'], $site['pages'], $breadcrumbs);
        $rsp['pagelist'] = flattenMenu($rsp['pagelist'], 0, $site['orphans'], $site['pages'], null, $skiplist, '-- ');
//        $rsp['orphanpages'] = array();
//        foreach($site['orphans'] as $page_id) {
//            $rsp['orphanpages'][] = array('id' => $page_id, 'name' => $site['pages'][$page_id]['title']);
//            if( isset($site['pages'][$page_id]['children']) ) {
//                $rsp['orphanpages'] = flattenMenu($rsp['orphanpages'], 1, $rsp[''], $site['pages'], null);
//                foreach($site['pages'][$page_id]['children'] as $child_id) {
//                    $rsp['orphanpages'] = flattenMenu($rsp['orphanpages'], 1, $site['pages'][$page_id]['children'], $site['pages'], null);
//                }
//            }
//        }
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

        $rsp['page']['page_url'] = $site['base_url'] . $rc['page']['path'];
    }

    //
    // Check which account menu items are available
    //
    if( isset($args['view']) && $args['view'] == 'account' ) {
        $items = array();
        $rsp['account-menuitems'] = array();
        foreach($ciniki['tenant']['modules'] as $module => $m) {
            list($pkg, $mod) = explode('.', $module);
            $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'wng', 'accountMenuItems');
            if( $rc['stat'] == 'ok' ) {
                $name = $pkg . ' ' . $mod;
                $info_filename = $ciniki['config']['ciniki.core']['root_dir'] . "/{$pkg}-mods/{$mod}/_info.ini";
                if( file_exists($info_filename) ) {
                    $info = parse_ini_file($info_filename);
                    if( isset($info['wng-name']) && $info['wng-name'] != '' ) {
                        $name = $info['wng-name'];
                    } elseif( isset($info['name']) && $info['name'] != '' ) {
                        $name = $info['name'];
                    } 
                }
                $rsp['account-menuitems'][] = array(
                    'pkg' => $pkg,
                    'mod' => $mod,
                    'name' => $name,
                    );
            }
        }
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
