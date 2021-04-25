<?php
//
// Description
// -----------
// Update the paths for each page on the website
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_pathsUpdate(&$ciniki, $tnid, $site_id) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'siteLoad');
    $rc = ciniki_wng_siteLoad($ciniki, $tnid, $site_id);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.99', 'msg'=>'Unable to load site', 'err'=>$rc['err']));
    }
    $site = isset($rc['site']) ? $rc['site'] : array();


    function recurse($path, $updates, $site, $pages, $page_id) {
        // Make sure not homepage, it cannot be changed.
        if( $pages[$page_id]['parent_id'] > 0 ) {
            $path .= '/' . $pages[$page_id]['permalink'];
            if( $pages[$page_id]['path'] != $path ) {
                $updates[$page_id] = $path;
            }
        }
        if( isset($pages[$page_id]['children']) ) {
            foreach($pages[$page_id]['children'] as $cid => $child_id) {
                $updates = recurse($path, $updates, $site, $pages, $child_id);
            }
        }
        return $updates;
    }
    $updates = recurse('', array(), $site, $site['pages'], $site['homepage_id']);
  
    foreach($updates as $page_id => $path) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
        $rc = ciniki_core_objectUpdate($ciniki, $tnid, 'ciniki.wng.page', $page_id, array(
            'path' => $path,
            ), 0x04);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.59', 'msg'=>'Unable to update the page', 'err'=>$rc['err']));
        }
    }

    return array('stat'=>'ok');
}
?>
