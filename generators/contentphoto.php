<?php
//
// Description
// -----------
// Generate the HTML for a block that display a picture and paragraph. 
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_contentphoto(&$ciniki, $tnid, $request, $block) {

    $content = '';

    $image_position = 'top';
    if( isset($block['image-position']) && in_array($block['image-position'], ['bottom-left', 'bottom-right']) ) {
        $image_position = 'bottom';
    }



    if( $block['content'] != '' ) {
        $content .= "<div class='block-contentphoto"
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . (isset($block['image-position']) && $block['image-position'] != '' ? ' image-' . $block['image-position'] : ' image-top-right')
            . "'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";

        if( isset($block['image-id']) && $block['image-id'] > 0 && $image_position == 'top' ) {
            //
            // Copy image to cache
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheImageAdd');
            $rc = ciniki_wng_cacheImageAdd($ciniki, $tnid, $request['site'], array( 
                'image_id' => $block['image-id'],
                'version' => 'original',
                'maxwidth' => 2048,
                ));
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.87', 'msg'=>'', 'err'=>$rc['err']));
            }

            //
            // Make sure the image is in the cache
            //
            $content .= "<div class='image-wrap'>";
            $content .= "<img alt='" . (isset($block['title']) ? $block['title'] : '') . "' src='" . $rc['url'] . "' />";
            $content .= '</div>';
        }

        $content .= "<div class='content-wrap'>"; 
        if( isset($block['title']) && $block['title'] != '' ) {
            $content .= "<h2>" . $block['title'] . "</h2>";
        }
        if( isset($block['content']) && $block['content'] != '' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['content']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.88', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= $rc['content'];
        }
        if( isset($block['button-1-text']) && $block['button-1-text'] != '' 
            && isset($block['button-1-url']) && $block['button-1-url'] != '' 
            ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'urlProcess');
            $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, $block['button-1-url']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.89', 'msg'=>'', 'err'=>$rc['err']));
            }
            $content .= "<a class='button' href='" . $rc['url'] . "'>" . $block['button-1-text'] . "</a>";
        }
        if( isset($block['button-2-text']) && $block['button-2-text'] != '' 
            && isset($block['button-2-url']) && $block['button-2-url'] != '' 
            ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'urlProcess');
            $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, $block['button-2-url']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.89', 'msg'=>'', 'err'=>$rc['err']));
            }
            $content .= "<a class='button' href='" . $rc['url'] . "'>" . $block['button-2-text'] . "</a>";
        }
        $content .= '</div>';

        if( isset($block['image-id']) && $block['image-id'] > 0 && $image_position == 'bottom' ) {
            //
            // Copy image to cache
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheImageAdd');
            $rc = ciniki_wng_cacheImageAdd($ciniki, $tnid, $request['site'], array( 
                'image_id' => $block['image-id'],
                'version' => 'original',
                'maxwidth' => 2048,
                ));
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.87', 'msg'=>'', 'err'=>$rc['err']));
            }

            //
            // Make sure the image is in the cache
            //
            $content .= "<div class='image-wrap'>";
            $content .= "<img alt='" . (isset($block['title']) ? $block['title'] : '') . "' src='" . $rc['url'] . "' />";
            $content .= '</div>';
        }

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }



    return array('stat'=>'ok', 'content'=>$content);
}
?>
