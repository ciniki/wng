<?php
//
// Description
// -----------
// This function will walk the public pages of the site and index each section
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_siteIndexUpdate(&$ciniki, $tnid, $args) {

    //
    // Make sure a site has been specified
    //
    if( !isset($args['site_id']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.186', 'msg'=>'No site specified'));
    }

    //
    // Load the site
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'siteLoad');
    $rc = ciniki_wng_siteLoad($ciniki, $tnid, $args['site_id'], 'no');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.187', 'msg'=>'Unable to load site', 'err'=>$rc['err']));
    }
    $site = $rc['site'];

    //
    // Check if old index should be cleared first
    //
    $indexed_images = array();
    if( is_dir($site['cache_dir'] . '/search') ) {
        $dh = opendir($site['cache_dir'] . '/search');
        while($file = readdir($dh)) {
            if( $file == '.' || $file == '..' ) {
                continue;
            }
            if( is_file($site['cache_dir'] . '/search/' . $file) 
                && preg_match('/^0*([1-9][0-9]+)\.jpg/', $file, $m) 
                ) {
                $indexed_images[] = $m[1];
            }
        }
    }
    if( isset($args['clear']) && $args['clear'] == 'yes' ) {
        //
        // Get a list of all the images
        //
/*        if( is_dir($site['cache_dir'] . '/search') ) {
            $dh = opendir($site['cache_dir'] . '/search');
            while($file = readdir($dh)) {
                if( $file == '.' || $file == '..' ) {
                    continue;
                }
                if( is_file($site['cache_dir'] . '/search/' . $file) ) {
                    unlink($site['cache_dir'] . '/search/' . $file);
                }
            }
            rmdir($site['cache_dir'] . '/search');
        } */

        //
        // Clear the index
        //
        $strsql = "DELETE FROM ciniki_wng_index "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND site_id = '" . ciniki_core_dbQuote($ciniki, $args['site_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
        $rc = ciniki_core_dbDelete($ciniki, $strsql, 'ciniki.wng');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.194', 'msg'=>'Unable to clear index', 'err'=>$rc['err']));
        }
    }

    //
    // Make sure cache search dir exists
    //
    if( !file_exists($site['cache_dir'] . '/search') ) {
        mkdir($site['cache_dir'] . '/search');
    }

    //
    // Load the existing indexed sections
    //
    $strsql = "SELECT DISTINCT section_id "
        . "FROM ciniki_wng_index "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND site_id = '" . ciniki_core_dbQuote($ciniki, $args['site_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.wng', array(
        array('container'=>'sections', 'fname'=>'section_id', 'fields'=>array('id' => 'section_id')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.189', 'msg'=>'Unable to load indexed sections', 'err'=>$rc['err']));
    }
    $indexed_sections = isset($rc['sections']) ? $rc['sections'] : array();

    //
    // Keep track of pages that have been indexed, so we don't duplicate
    //
    $site['indexed_sections'] = array();
    $site['indexed_pages'] = array();
    $site['indexed_images'] = array();
    $base_url = '';

    $site['start_time'] = time();
    

    //
    // Go through the header menu and footer menu and build a list of pages to process
    //
    $pages = array();
    foreach($site['headermenu'] as $page) {
        if( !in_array($page, $pages) ) {
            $pages[] = $page;
        }
    }
    foreach($site['footermenu'] as $page) {
        if( !in_array($page, $pages) ) {
            $pages[] = $page;
        }
    }

    //
    // Update index for each page
    //
    foreach($pages as $page_id) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'pageIndexUpdate');
        $rc = ciniki_wng_pageIndexUpdate($ciniki, $tnid, $site, array(
            'page_id' => $page_id,
            'index_flags' => 0,
            'base_url' => $base_url,
            ));
        if( $rc['stat'] != 'ok' ) {
            if( ($site['start_time']+25) < time() ) {
                return array('stat'=>'outatime', 'err'=>array('code'=>'ciniki.wng.226', 'msg'=>'outatime'));
            }
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.190', 'msg'=>'Unable to update index for page', 'err'=>$rc['err']));
        }
    }

    //
    // Check for sections to delete
    //
    foreach($indexed_sections as $section) {
        if( !in_array($section['id'], $site['indexed_sections']) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'sectionIndexDelete');
            $rc = ciniki_wng_sectionIndexDelete($ciniki, $tnid, $site, $section);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.221', 'msg'=>'Unable to remove section', 'err'=>$rc['err']));
            }
        }
    }

    //
    // Check for images that need to be deleted
    //
    $site['indexed_images'] = array_unique($site['indexed_images']);
    foreach($indexed_images as $image_id) {
        if( !in_array($image_id, $site['indexed_images']) ) {
            $rc = ciniki_wng_objectImageIndexDelete($ciniki, $tnid, $site, $image_id);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.224', 'msg'=>'Unable to remove image', 'err'=>$rc['err']));
            }
        }
    }

    return array('stat'=>'ok');
}
?>
