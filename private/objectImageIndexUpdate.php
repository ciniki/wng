<?php
//
// Description
// -----------
// This function will update the indexed object image in the wng cache so it's ready for search results.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_objectImageIndexUpdate(&$ciniki, $tnid, &$site, $image_id, $index_id) {

    if( $image_id <= 0 ) {
        return array('stat'=>'ok');
    }

    $site['indexed_images'][] = $image_id;

    //
    // Load last_updated date to check against the cache
    //
    $strsql = "SELECT id, type, UNIX_TIMESTAMP(ciniki_images.last_updated) AS last_updated "
        . "FROM ciniki_images "
        . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $image_id) . "' "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.images', 'image');
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.207', 'msg'=>'Unable to load image', 'err'=>$rc['err']));
    }
    if( !isset($rc['image']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.208', 'msg'=>'Unable to load image'));
    }
    $img = $rc['image'];

    //
    // Force output to be jpg
    //
    $extension = 'jpg';

    //
    // Get the cache directory
    //
    $filename = $site['cache_dir'] . '/search/' . sprintf("%012d", $image_id) . '.' . $extension;

    if( !file_exists($filename) || filemtime($filename) < $img['last_updated'] ) {
        //
        // Load the image from the database
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadImage');
        $rc = ciniki_images_loadImage($ciniki, $tnid, $img['id'], 'original');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $image = $rc['image'];

        //
        // Scale image
        //
        $image->scaleImage(600, 0);

        //
        // Write the file
        //
        $h = fopen($filename, 'w');
        if( $h ) {
            if( $img['type'] == 2 ) {
                $image->setImageFormat('jpeg');
            } 
            $image->setImageCompressionQuality(60);
            fwrite($h, $image->getImageBlob());
            fclose($h);
        } else {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.209', 'msg'=>'Unable to load image'));
        }
    }

    return array('stat'=>'ok');
}
?>
