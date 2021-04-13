<?php
//
// Description
// -----------
// Arguments
// ---------
// ciniki:
// image_id:        The ID of the image in the images module to prepare for the website.
// version:         The version of the image, original or thumbnail.  Thumbnail down not
//                  refer to the size, but the square cropped version of the original.
// maxwidth:        The maximum width the rendered photo should be.
// maxheight:       The maximum height the rendered photo should be.
// quality:         The quality setting for jpeg output.  The default if unspecified is 60.
//
// Returns
// -------
//
function ciniki_wng_cacheImageAdd($ciniki, $tnid, $site, $args) {

    if( !isset($args['image_id']) || $args['image_id'] == '' || $args['image_id'] == 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.75', 'msg'=>'No image specified'));
    }

    $version = isset($args['version']) ? $args['version'] : 'original';
    $maxwidth = isset($args['maxwidth']) ? $args['maxwidth'] : 0;
    $maxheight = isset($args['maxheight']) ? $args['maxheight'] : 0;
    $quality = isset($args['quality']) ? $args['quality'] : 60;


    if( $maxwidth == 0 && $maxheight == 0 ) {
        $size = 'o';
    } elseif( $maxwidth == 0 ) {
        $size = 'h' . $maxheight;
    } else {
        $size = 'w' . $maxwidth;
    }

    //
    // Load the image
    //
    $strsql = "SELECT id, uuid, type, UNIX_TIMESTAMP(ciniki_images.last_updated) AS last_updated "
        . "FROM ciniki_images "
        . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $args['image_id']) . "' "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.images', 'image');
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.81', 'msg'=>'Unable to load image', 'err'=>$rc['err']));
    }
    if( !isset($rc['image']) ) {
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.wng.82', 'msg'=>'The image you requested does not exist.'));
    }
    $img = $rc['image'];

    //
    // Build working path, and final url
    //
    if( $img['type'] == 2 ) {
        $extension = 'png';
    } else {
        $extension = 'jpg';
    }
    if( $maxwidth == 0 && $maxheight == 0 ) {
        $filename = '/o/' . $img['uuid'] . '.' . $extension;
        $size = 'o';
    } elseif( $maxwidth == 0 ) {
        $filename = '/h' . $maxheight . '/' . $img['uuid'] . '.' . $extension;
        $size = 'h' . $maxheight;
    } else {
        $filename = '/w' . $maxwidth . '/' . $img['uuid'] . '.' . $extension;
        $size = 'w' . $maxwidth;
    }
    $img_filename = $site['cache_dir'] . '/images' . $filename;
    $img_url = $site['cache_url'] . '/images' . $filename;

    //
    // Check last_updated against the file timestamp, if the file exists
    //
    if( !file_exists($img_filename) || filemtime($img_filename) < $img['last_updated'] ) {

        //
        // Load the image from the database
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadImage');
        $rc = ciniki_images_loadImage($ciniki, $tnid, $img['id'], $version);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $image = $rc['image'];

        //
        // Scale image
        //
        if( ($maxwidth > 0 && $maxwidth < $image->getImageWidth()) 
            || ($maxheight > 0 && $maxheight < $image->getImageHeight()) 
            ) {
            $image->scaleImage($maxwidth, $maxheight);
        }

        //
        // Check if directory exists
        //
        if( !file_exists(dirname($img_filename)) ) {
            mkdir(dirname($img_filename), 0755, true);
        }

        //
        // Write the file
        //
        $h = fopen($img_filename, 'w');
        if( $h ) {
            if( $img['type'] == 2 ) {
                $image->setImageFormat('png');
            } else {
                $image->setImageCompressionQuality($quality);
            }
            fwrite($h, $image->getImageBlob());
            fclose($h);
        } else {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.85', 'msg'=>'Unable to load image'));
        }
    }

    return array('stat'=>'ok', 'url'=>$img_url, 'filename'=>$img_filename);
}
?>
