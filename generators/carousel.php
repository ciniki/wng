<?php
//
// Description
// -----------
// carousel
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_carousel(&$ciniki, $tnid, $request, $block) {

    $content = '';
        
    $content .= "<div class='block-carousel"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content' id='carousel-items'>";

    $class = 'current';
    foreach($block['items'] as $iid => $item) {
        if( isset($item['image-id']) && $item['image-id'] > 0 ) {
            //
            // Copy image to cache
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheImageAdd');
            $rc = ciniki_wng_cacheImageAdd($ciniki, $tnid, $request['site'], array( 
                'image_id' => $item['image-id'],
                'version' => 'original',
                ));
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.87', 'msg'=>'', 'err'=>$rc['err']));
            }

            //
            // Check if this should be setup as last item
            //
            if( $iid == count($block['items']) ) {
                $class = 'prev';
            }
            $content .= "<div class='item {$class}'>";
            $content .= "<div class='item-wrap'>";
            if( isset($item['url']) ) {
                $content .= "<a href='" . $item['url'] . "' />";
            }
            $content .= "<div class='image'>";
            $content .= "<img alt='" . (isset($image['title']) ? $image['title'] : '') . "' src='" . $rc['url'] . "'>";
            $content .= '</div>';
    
            if( isset($block['titles']) && $block['titles'] == 'yes' ) {
                $content .= "<div class='title'>";
                if( isset($item['title']) ) {
                    $content .= $item['title'];
                } else {
                    $content .= '&nbsp;';
                }
                $content .= '</div>';
            }



            if( isset($item['url']) ) {
                $content .= "</a>";
            }
            $content .= '</div>';
            $content .= '</div>';

            if( $class == 'current' ) {
                $class = 'next';
            } elseif( $class == 'next' ) {
                $class = '';
            }
        }
    }

    $content .= '</div>';

    //
    // Buttons for controlling slider
    //
    $content .= "<div class='buttons'>";

    $content .= "<div class='button-wrap prev'>";
    $content .= "<div class='button' onclick='C.carousel.prev();'>";
//    $content .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 512"><path d="M31.7 239l136-136c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9L127.9 256l96.4 96.4c9.4 9.4 9.4 24.6 0 33.9L201.7 409c-9.4 9.4-24.6 9.4-33.9 0l-136-136c-9.5-9.4-9.5-24.6-.1-34z"/></svg>';
    $content .= '<svg viewBox="0 0 80 80" stroke="#fff" fill="none"><polyline stroke-width="5" stroke-linecap="round" stroke-linejoin="round" points="50,70 20,40 50,10"/></svg>';
    $content .= "</div>";
    $content .= '</div>';

    $content .= "<div class='button-wrap next'>";
    $content .= "<div class='button' onclick='C.carousel.next();'>";
    $content .= '<svg viewBox="0 0 80 80" stroke="#fff" fill="none"><polyline stroke-width="5" stroke-linecap="round" stroke-linejoin="round" points="30,70 60,40 30,10"/></svg>';
    //$content .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 512"><path d="M224.3 273l-136 136c-9.4 9.4-24.6 9.4-33.9 0l-22.6-22.6c-9.4-9.4-9.4-24.6 0-33.9l96.4-96.4-96.4-96.4c-9.4-9.4-9.4-24.6 0-33.9L54.3 103c9.4-9.4 24.6-9.4 33.9 0l136 136c9.5 9.4 9.5 24.6.1 34z"/></svg>';
    $content .= "</div>";
    $content .= '</div>';

    $content .= '</div>';

    //
    // Close out block
    //
    $content .= '</div>';
    $content .= '</div>';

    return array('stat'=>'ok', 'content'=>$content);
}
?>
