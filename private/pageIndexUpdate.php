<?php
//
// Description
// -----------
// Update the index for the website page
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_pageIndexUpdate(&$ciniki, $tnid, &$site, $args) {


    //
    // Make sure requested page exists
    //
    if( !isset($site['pages'][$args['page_id']])) {
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.191', 'msg'=>'Invalid page'));
        }
    }
//    error_log("Processing Page: " . $site['pages'][$args['page_id']]['title']);

    //
    // Check if it's already been indexed
    //
    if( in_array($args['page_id'], $site['indexed_pages']) ) {
        return array('stat'=>'ok');
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'pageLoad');
    $rc = ciniki_wng_pageLoad($ciniki, $tnid, array('site'=>$site), $args['page_id']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.192', 'msg'=>'Unable to load page', 'err'=>$rc['err']));
    }
    $page = $rc['page'];

    //
    // Make sure the page is visible
    //
    if( ($page['flags']&0x01) == 0 ) {
        $site['indexed_pages'][] = $page['id'];
        return array('stat'=>'ok');
    }

    $index_flags = isset($args['index_flags']) ? $args['index_flags'] : 0;
    //
    // Check if visible only to logged in customer/members
    //
    if( ($page['flags']&0x02) == 0x02 ) {
        $index_flags |= 0x02;
    }
    //
    // Check if visible only to logged in members
    //
    if( ($page['flags']&0x04) == 0x04 ) {
        $index_flags |= 0x04;
    }

    $base_url = $args['base_url'];
    if( $page['parent_id'] != 0 ) {
        $base_url = $base_url . '/' . $page['permalink'];
    }


//    error_log(print_r($site,true));
    //
    // Process the sections on this page
    //
    foreach($page['sections'] as $section) {
        
        //
        // Check if section is hidden
        //
        if( ($section['flags']&0x10) == 0x10 ) {
            continue;
        }

        if( in_array($section['id'], $site['indexed_sections']) ) {
            continue;
        }
        
        //
        // Check if buttons linking to other pages
        // **NOTE**: This may change in the future to be call functions in modules to get page list for section
        // OR return the list of sub pages to be processed from sectionIndexUpdate
        //
        
        for($i = 1; $i <= 30; $i++) {
            if( isset($section['settings']["button-{$i}-page"])
                && $section['settings']["button-{$i}-page"] > 0 
                && isset($section['settings']["button-{$i}-text"])
                && $section['settings']["button-{$i}-text"] != '' 
                ) {
                $rc = ciniki_wng_pageIndexUpdate($ciniki, $tnid, $site, array(  
                    'page_id' => $section['settings']["button-{$i}-page"],
                    'index_flags' => $index_flags,
                    'base_url' => $base_url,
                    ));
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.195', 'msg'=>'Unable to index page', 'err'=>$rc['err']));
                }
            }
            if( isset($section['settings']["link-{$i}-page"])
                && $section['settings']["link-{$i}-page"] > 0 
                && isset($section['settings']["link-{$i}-text"])
                && $section['settings']["link-{$i}-text"] != '' 
                ) {
                $rc = ciniki_wng_pageIndexUpdate($ciniki, $tnid, $site, array(  
                    'page_id' => $section['settings']["link-{$i}-page"],
                    'index_flags' => $index_flags,
                    'base_url' => $base_url,
                    ));
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.196', 'msg'=>'Unable to index page', 'err'=>$rc['err']));
                }
            }
        }

        $section['index_flags'] = $index_flags;
        $section['base_url'] = $base_url;
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'sectionIndexUpdate');
        $rc = ciniki_wng_sectionIndexUpdate($ciniki, $tnid, $site, $section);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.193', 'msg'=>'Unable to index section', 'err'=>$rc['err']));
        }
    }

    $site['indexed_pages'][] = $page['id'];


/*    //
    // Load the page indexed objects for this section
    //
    $strsql = "SELECT id, "
        . "site_id, "
        . "section_id, "
        . "label, "
        . "title, "
        . "subtitle, "
        . "meta, "
        . "primary_image_id, "
        . "object, "
        . "object_id, "
        . "primary_words, "
        . "secondary_words, "
        . "tertiary_words, "
        . "weight, "
        . "url "
        . "FROM ciniki_wng_index AS index "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND site_id = '" . ciniki_core_dbQuote($ciniki, $args['site_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.wng', array(
        array('container'=>'sections', 'fname'=>'section_id', 
            'fields'=>array('id' => 'section_id'),
            ),
        array('container'=>'index', 'fname'=>'id', 
            'fields'=>array('id' => 'section_id'),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.188', 'msg'=>'Unable to load ', 'err'=>$rc['err']));
    } */


    return array('stat'=>'ok');
}
?>
