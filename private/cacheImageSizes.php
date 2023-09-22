<?php
//
// Description
// -----------
// This function will create a series of cached images for original and webp formats. 
// The CSS is returned in reverse order so it functions properly with CSS rules.
//
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
function ciniki_wng_cacheImageSizes($ciniki, $tnid, $site, $args) {

    if( !isset($args['image_id']) || $args['image_id'] == '' || $args['image_id'] == 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.180', 'msg'=>'No image specified'));
    }

    $sizes = isset($args['sizes']) ? explode(',', $args['sizes']) : array();
    $webp = isset($args['webp']) && $args['webp'] == 'yes' ? 'yes' : 'no';

    $srcset = '';
    $bg_set = '';
    $bg_css = '';
    $maxwidth = $args['maxwidth'];

    //
    // Create the various sizes
    //
    foreach($sizes as $size) {
        $size_css = '';
        if( $webp == 'yes' ) {
            $args['format'] = 'webp';
            $args['maxwidth'] = $size;
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheImageAdd');
            $rc = ciniki_wng_cacheImageAdd($ciniki, $tnid, $site, $args);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.235', 'msg'=>'', 'err'=>$rc['err']));
            }
            $default_url = $rc['url'];
            $srcset .= ($srcset != '' ? ', ' : '') . "{$rc['url']} {$size}w";
            $size_css .= ($size_css != '' ? ',':'') . "url({$rc['url']})";
            unset($args['format']);
        }
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheImageAdd');
        $rc = ciniki_wng_cacheImageAdd($ciniki, $tnid, $site, $args);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.235', 'msg'=>'', 'err'=>$rc['err']));
        }
        $default_url = $rc['url'];
        $srcset .= ($srcset != '' ? ', ' : '') . "{$rc['url']} {$size}w";
        $size_css .= ($size_css != '' ? ',':'') . "url({$rc['url']})";

        if( isset($args['css_selector']) ) {
            $bg_css = "@media screen and (max-width: {$size}px) {"
                . $args['css_selector'] . " {"
                    . "background-image: -webkit-image-set("
                        . $size_css
                    . ");"
                . "}}\n"
                . $bg_css;
        }
    }

    //
    // Create default webp
    //
    $args['maxwidth'] = $maxwidth;
    $size_css = '';
    if( $webp == 'yes' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheImageAdd');
        $args['format'] = 'webp';
        $rc = ciniki_wng_cacheImageAdd($ciniki, $tnid, $site, $args);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.235', 'msg'=>'', 'err'=>$rc['err']));
        }
        $srcset .= ($srcset != '' ? ', ' : '') . "{$rc['url']}" . (isset($args['maxwidth']) && $args['maxwidth'] > 0 ? " {$args['maxwidth']}w" : '');
        $bg_set .= ($bg_set != '' ? ', ' : '') . "url({$rc['url']})";
        $size_css .= ($size_css != '' ? ',':'') . "url({$rc['url']})";
        unset($args['format']);
    }

    //
    // Create the default
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheImageAdd');
    $rc = ciniki_wng_cacheImageAdd($ciniki, $tnid, $site, $args);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.235', 'msg'=>'', 'err'=>$rc['err']));
    }
    $default_url = $rc['url'];
    $srcset .= ($srcset != '' ? ', ' : '') . "{$rc['url']}" . (isset($args['maxwidth']) && $args['maxwidth'] > 0 ? " {$args['maxwidth']}w" : '');
    // Only add to background set if already items there.
    // Don't want only 1 in background set.
    if( $bg_set != '' ) {
        $bg_set .= ", url({$rc['url']})";
    }
    $size_css .= ($size_css != '' ? ',':'') . "url({$rc['url']})";

    //
    // Setup the background css
    //
    if( isset($args['css_selector']) ) {
        $bg_css = $args['css_selector'] . " {"
                . "background-image: url({$rc['url']});"
                . "background-image: -webkit-image-set("
                . $size_css
                . ");}\n"
                . $bg_css;
    }

    return array('stat'=>'ok', 'url'=>$default_url, 'srcset'=>$srcset, 'bg_set'=>$bg_set, 'bg_css'=>$bg_css);
}
?>
