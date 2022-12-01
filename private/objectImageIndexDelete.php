<?php
//
// Description
// -----------
// This function will remove the indexed object image in the wng cache so it's ready for search results.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_objectImageIndexDelete(&$ciniki, $tnid, &$site, $image_id) {

    if( $image_id <= 0 ) {
        return array('stat'=>'ok');
    }

    //
    // Get the cache directory
    //
    $filename = $site['cache_dir'] . '/search/' . sprintf("%012d", $image_id) . '.jpg';

    if( file_exists($filename) ) {
        unlink($filename);
    }

    if( $key = array_search($image_id, $site['indexed_images']) !== false ) {
        unset($site['indexed_images'][$key]);
    }

    return array('stat'=>'ok');
}
?>
