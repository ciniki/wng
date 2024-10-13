<?php
//
// Description
// -----------
// Load the page details and sections
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_pageLoad(&$ciniki, $tnid, $request, $page_id) {

    //
    // Get the page details
    //
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
        . "WHERE ciniki_wng_pages.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_wng_pages.id = '" . ciniki_core_dbQuote($ciniki, $page_id) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'page');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.22', 'msg'=>'Unable to load page', 'err'=>$rc['err']));
    }
    if( !isset($rc['page']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.23', 'msg'=>'Unable to find requested page'));
    }
    $page = $rc['page'];

    //
    // Load the sections
    //
    $strsql = "SELECT ciniki_wng_sections.id, "
        . "ciniki_wng_sections.page_id, "
        . "ciniki_wng_sections.sequence, "
        . "ciniki_wng_sections.flags, "
        . "ciniki_wng_sections.ref, "
        . "ciniki_wng_sections.label, "
        . "ciniki_wng_sections.settings "
        . "FROM ciniki_wng_sections "
        . "WHERE ciniki_wng_sections.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_wng_sections.page_id = '" . ciniki_core_dbQuote($ciniki, $page_id) . "' "
        . "AND (ciniki_wng_sections.flags&0x13) = 0 " // Body sections only, no header or footer and Visible
        . "ORDER BY sequence "
        . "";
    // This must be ArrayTree as this is passed back to UI and javascript will sort on ID
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wng', array(
        array('container'=>'sections', 'fname'=>'id', 
            'fields'=>array('id', 'page_id', 'sequence', 'flags', 'ref', 'label', 'settings')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $page['sections'] = isset($rc['sections']) ? $rc['sections'] : array();
    $tnum = 1;
    foreach($page['sections'] as $sid => $section) {
        if( $section['settings'] != '' ) {
            if( isset($section['settings'][0]) && $section['settings'][0] == '{' ) {
                $page['sections'][$sid]['settings'] = json_decode($section['settings'], true);
            } elseif( isset($section['settings'][0]) && $section['settings'][0] == '[' ) {
                $page['sections'][$sid]['settings'] = json_decode($section['settings'], true);
            } else {
                // FIXME: Remove old serialize handler
                $section['settings'] = str_replace("\xC3\xA2\xE2\x82\xAC\xE2\x80\x9C", '-', $section['settings']);
                $section['settings'] = utf8_decode($section['settings']);
                $section['settings'] = preg_replace_callback('!s:(\d+):"(.*?)";!s', function($m) {
                    return 's:' . strlen($m[2]) . ':"' . $m[2] . '";';
                    }, $section['settings']);
                $page['sections'][$sid]['settings'] = unserialize($section['settings']);
            }
            if( $page['sections'][$sid]['settings'] === false ) {
                error_log("Problem with settings for section: " . $section['id']);
            }
        } else {
            $page['sections'][$sid]['settings'] = array();
        }
        //
        // For any sections that have titles, add title_sequence to the section.
        // This is used to determine if the heading should be an h1 or h2.
        //
        if( isset($page['sections'][$sid]['settings']['title']) && $page['sections'][$sid]['settings']['title'] != '' ) {
            $page['sections'][$sid]['title_sequence'] = $tnum++;
        }
    }

    return array('stat'=>'ok', 'page'=>$page);
}
?>
