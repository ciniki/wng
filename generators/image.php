<?php
//
// Description
// -----------
// 
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_image(&$ciniki, $tnid, $request, $block) {

    $content = '';

    if( isset($block['image-id']) && $block['image-id'] > 0 ) {
        $content .= "<div class='block-image"
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . (!isset($block['image-id']) || $block['image-id'] == 0 ? ' no-image' : '')
            . "'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";
       
        if( isset($block['sequence']) && $block['sequence'] == 1 && isset($block['title']) && $block['title'] != '' ) {
            $content .= "<h1>" . $block['title'] . "</h1>";
        } elseif( isset($block['title']) && $block['title'] != '' ) {
            $content .= "<h2>" . $block['title'] . "</h2>";
        }

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
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.104', 'msg'=>'', 'err'=>$rc['err']));
        }

        //
        // Make sure the image is in the cache
        //
        $content .= "<div class='image-wrap'>";
        $content .= "<img alt='" . (isset($block['title']) ? $block['title'] : '') . "' src='" . $rc['url'] . "' />";
        $content .= '</div>';

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
