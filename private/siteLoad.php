<?php
//
// Description
// -----------
// Load all the details about a site for header, menu, breadcrumbs, footer.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_siteLoad(&$ciniki, $tnid, $site_id, $theme='no') {

    //
    // Load the site and home page
    //
    $strsql = "SELECT sites.id, "
        . "sites.tnid, "
        . "sites.uuid, "
        . "sites.domain_id, "
        . "sites.name, "
        . "sites.status, "
        . "sites.permalink, "
        . "sites.flags, "
        . "sites.theme, "
        . "sites.lang, "
        . "sites.css_classes "
        . "FROM ciniki_wng_sites AS sites "
//        . "LEFT JOIN ciniki_tenant_domains AS domains ON ("
//            . "sites.domain_id = domains.id "
//            . "AND domains.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
//            . ") "
        . "WHERE sites.id = '" . ciniki_core_dbQuote($ciniki, $site_id) . "' "
        . "AND sites.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'site');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.4', 'msg'=>'Unable to load site', 'err'=>$rc['err']));
    }
    if( !isset($rc['site']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.20', 'msg'=>'Unable to load site'));
    }
    $site = $rc['site'];
    $site['uuid_dir'] = $rc['site']['uuid'][0] . '/' . $rc['site']['uuid'];

    //
    // Setup cache directory
    //
    $site['cache_url'] = '/ciniki-wng-cache/' . $site['uuid_dir'];
    if( isset($ciniki['config']['ciniki.wng']['cdn.domain']) && $ciniki['config']['ciniki.wng']['cdn.domain'] != '' ) {
        $site['cache_url'] = '//' . $ciniki['config']['ciniki.wng']['cdn.domain'] . $site['cache_url'];
    }
    $site['cache_dir'] = $ciniki['config']['ciniki.core']['modules_dir'] . '/wng/cache/' . $site['uuid_dir'];
    if( !is_dir($site['cache_dir']) ) {
        if( !mkdir($site['cache_dir'], 0755, true) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.8', 'msg'=>'Unable to prepare site'));
        }
    }

    //
    // Load the settings
    //
    $strsql = "SELECT detail_key, detail_value "
        . "FROM ciniki_wng_settings "
        . "WHERE site_id = '" . ciniki_core_dbQuote($ciniki, $site_id) . "' "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    //
    // By default, exclude long theme settings. Only required when rebuilding css
    // or by the UI.
    //
    if( $theme == 'no' ) {
        $strsql .= "AND detail_key NOT LIKE 'theme-%' ";
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList2');
    $rc = ciniki_core_dbQueryList2($ciniki, $strsql, 'ciniki.wng', 'settings');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.12', 'msg'=>'Unable to load site', 'err'=>$rc['err']));
    }
    $site['settings'] = isset($rc['settings']) ? $rc['settings'] : array();
    
    //
    // Load the sitemap
    //
    $strsql = "SELECT pages.id, "
        . "pages.parent_id, "
        . "pages.ptype, "
        . "pages.sequence, "
        . "pages.title, "
        . "pages.permalink, "
        . "pages.menu_flags, "
        . "pages.flags, "
        . "pages.path, "
        . "pages.redirect_url "
        . "FROM ciniki_wng_pages AS pages "
        . "WHERE pages.site_id = '" . ciniki_core_dbQuote($ciniki, $site_id) . "' "
        . "AND pages.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "ORDER BY sequence, path "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.wng', array(
        array('container'=>'pages', 'fname'=>'id', 
            'fields'=>array('id', 'parent_id', 'ptype', 'sequence', 'title', 'permalink', 'menu_flags', 'flags', 'path', 'redirect_url'),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.94', 'msg'=>'Unable to load pages', 'err'=>$rc['err']));
    }
    $site['homepage_id'] = 0;
    $site['pages'] = isset($rc['pages']) ? $rc['pages'] : array();
    $site['headermenu'] = array();
    $site['footermenu'] = array();
    $site['paths'] = array();
    $site['orphans'] = array();
    foreach($site['pages'] as $pid => $page) {
        if( $page['parent_id'] == 0 && $site['homepage_id'] == 0 ) {
            $site['homepage_id'] = $pid;
        }
        $site['paths'][$page['path']] = $pid;
        if( $page['parent_id'] > 0 ) {
            if( isset($site['pages'][$page['parent_id']]) ) {
                if( !isset($site['pages'][$page['parent_id']]['children']) ) {
                    $site['pages'][$page['parent_id']]['children'] = array();
                }
                $site['pages'][$page['parent_id']]['children'][] = $pid;
            } else {
                $site['orphans'][] = $pid;
            }
        }
    }

    if( $site['homepage_id'] == 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.19', 'msg'=>'Missing home page'));
    }

    //
    // Setup the headermenu and footermenu
    //
    $add_homepage_id = $site['homepage_id'];
    if( isset($site['pages'][$site['homepage_id']]['children']) ) {
        foreach($site['pages'][$site['homepage_id']]['children'] as $child_pid) {
            if( $add_homepage_id > 0 
                && $site['pages'][$child_pid]['sequence'] >= $site['pages'][$add_homepage_id]['sequence'] 
                ) {
                $site['headermenu'][] = $add_homepage_id;
                $site['footermenu'][] = $add_homepage_id;
                $add_homepage_id = 0;
            }
            if( !isset($site['pages'][$child_pid]) ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.18', 'msg'=>'Missing child page'));
            }
            if( ($site['pages'][$child_pid]['menu_flags']&0x01) == 0x01 ) {
                $site['headermenu'][] = $child_pid;
            }
            if( ($site['pages'][$child_pid]['menu_flags']&0x02) == 0x02 ) {
                $site['footermenu'][] = $child_pid;
            }
            if( ($site['pages'][$child_pid]['menu_flags']&0xFF) == 0 ) {
                $site['orphans'][] = $child_pid;
            }
        }
    }
    if( $add_homepage_id > 0 ) {
        $site['headermenu'][] = $add_homepage_id;
        $site['footermenu'][] = $add_homepage_id;
        $add_homepage_id = 0;
    }

    //
    // Remove the Home from footermenu if only item in menu
    //
    if( isset($site['footermenu'][0]) && count($site['footermenu']) == 1 
        && $site['footermenu'][0] == $site['homepage_id'] 
        ) {
        $site['footermenu'] = array();
    }

    //
    // Get the sections for the header and footer
    //
    $site['headersections'] = array();
    $site['footersections'] = array();
    $strsql = "SELECT ciniki_wng_sections.id, "
        . "ciniki_wng_sections.page_id, "
        . "ciniki_wng_sections.sequence, "
        . "ciniki_wng_sections.flags, "
        . "ciniki_wng_sections.ref, "
        . "ciniki_wng_sections.label, "
        . "ciniki_wng_sections.settings "
        . "FROM ciniki_wng_sections "
        . "WHERE ciniki_wng_sections.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_wng_sections.page_id = '" . ciniki_core_dbQuote($ciniki, $site['homepage_id']) . "' "
        . "AND (ciniki_wng_sections.flags&0x03) > 0 "
        . "ORDER BY sequence "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wng', array(
        array('container'=>'sections', 'fname'=>'id', 
            'fields'=>array('id', 'page_id', 'sequence', 'flags', 'ref', 'label', 'settings')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['sections']) ) {
        foreach($rc['sections'] as $sid => $section) {
            if( isset($section['settings'][0]) && $section['settings'][0] == '{' ) {
                $section['settings'] = json_decode($section['settings'], true);
            } elseif( isset($section['settings'][0]) && $section['settings'][0] == '[' ) {
                $section['settings'] = json_decode($section['settings'], true);
            } else {
                $section['settings'] = str_replace("\xC3\xA2\xE2\x82\xAC\xE2\x80\x9C", '-', $section['settings']);
                $section['settings'] = utf8_decode($section['settings']);
                $section['settings'] = preg_replace_callback('!s:(\d+):"(.*?)";!s', function($m) {
                    return 's:' . strlen($m[2]) . ':"' . $m[2] . '";';
                    }, $section['settings']);
                $section['settings'] = unserialize($section['settings']);
            }
            if( ($section['flags']&0x01) == 0x01 ) {
                $site['headersections'][] = $section;
            }
            if( ($section['flags']&0x02) == 0x02 ) {
                $site['footersections'][] = $section;
            }
        }
    }

    return array('stat'=>'ok', 'site'=>$site);
}
?>
