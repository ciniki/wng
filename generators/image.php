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

        //
        // Check if next and previous buttons should be added
        //
        if( (isset($block['next']) && $block['next'] != '')
            || (isset($block['prev']) && $block['prev'] != '') 
            ) {
            //
            // The javascript will replace the current page location in the browser history and reload to new page.
            // This allows back button to go back to previous page, not previous image
            //
            $prev_url = '';
            $next_url = '';
            $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, 0, $block['prev']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.143', 'msg'=>'Unable to process button', 'err'=>$rc['err']));
            }
            $prev_url = $rc['url'];
            $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, 0, $block['next']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.143', 'msg'=>'Unable to process button', 'err'=>$rc['err']));
            }
            $next_url = $rc['url'];
            $content .= "<div class='buttons'>";
            $content .= "<div class='button-wrap prev'>";
            $content .= "<a class='button' href='javascript: window.location.replace(\"{$prev_url}\");'>";
            $content .= '<svg viewBox="0 0 80 80" stroke="#fff" fill="none"><polyline stroke-width="5" stroke-linecap="round" stroke-linejoin="round" points="50,70 20,40 50,10"/></svg>';
            $content .= "</a>";
            $content .= "</div>";
            $content .= "<div class='button-wrap next'>";
            $content .= "<a class='button' href='javascript: window.location.replace(\"{$next_url}\");'>";
            $content .= '<svg viewBox="0 0 80 80" stroke="#fff" fill="none"><polyline stroke-width="5" stroke-linecap="round" stroke-linejoin="round" points="30,70 60,40 30,10"/></svg>';
            $content .= "</a>";
            $content .= "</div>";

            $content .= "</div>";
        }

        $content .= '</div>';       // Close image-wrap

        if( isset($block['content']) && $block['content'] != '' ) {
            $content .= "<div class='content-wrap'>"; 
            if( isset($block['content']) && $block['content'] != '' ) {
                $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['content']);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.109', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
                }
                $content .= $rc['content'];
            } 
            $content .= "</div>";
        }

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
