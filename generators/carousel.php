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
function ciniki_wng_generators_carousel(&$ciniki, $tnid, &$request, $block) {

    $content = '';
   
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'urlProcess');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
    //
    // Skip if nothing
    //
    if( !isset($block['items']) || count($block['items']) < 1 ) {
        return array('stat'=>'ok', 'content'=>'');
    }

    //
    // Use the sequence number to give each carousel a unique id which 
    // allows several carousels on the same page
    //
    $carousel_id = isset($block['sequence']) ? $block['sequence'] : 1;

    $content .= "<div class='block-carousel"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content' id='carousel-items-{$carousel_id}'>";


    $class = 'current';

    $items = array_values($block['items']);
    foreach($items as $iid => $item) {
        if( isset($item['image-id']) && $item['image-id'] > 0 ) {
            //
            // Copy image to cache
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheImageAdd');
            $rc = ciniki_wng_cacheImageAdd($ciniki, $tnid, $request['site'], array( 
                'image_id' => $item['image-id'],
                'version' => 'original',
                'maxwidth' => '2048'
                ));
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.158', 'msg'=>'', 'err'=>$rc['err']));
            }
            $image = $rc;

            //
            // Create a webp version
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheImageAdd');
            $rc = ciniki_wng_cacheImageAdd($ciniki, $tnid, $request['site'], array( 
                'image_id' => $item['image-id'],
                'version' => 'original',
                'quality' => '90',
                'maxwidth' => '2048',
                'format' => 'webp',
                ));
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.158', 'msg'=>'', 'err'=>$rc['err']));
            }
            $webp = $rc;

            //
            // Check if this should be setup as last item
            //
            if( $iid == (count($block['items'])-1) && count($block['items']) > 2 ) {
                $class = 'prev';
            }
            $content .= "<div class='item {$class}'>";
            $content .= "<div class='item-wrap'>";
            $url = 'no';
            if( (isset($item['page']) && $item['page'] > 0) || (isset($item['url']) && $item['url'] != '') ) {
                $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request,
                    isset($item['page']) ? $item['page'] : 0,
                    isset($item['url']) ? $item['url'] : ''
                    );
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.157', 'msg'=>'Unable to process url', 'err'=>$rc['err']));
                }
                $content .= "<a target='" . $rc['target'] . "' "
                    . ((isset($item['aria-label']) && $item['aria-label'] != '') ? " aria-label=\"" . htmlentities($item['aria-label']) . "\"" : '')
                    . "href='" . $rc['url'] . "' />";
                $url = 'yes';
            }
            $content .= "<div class='image' style='background:#fff url(" . $image['url'] . ") "
                . (isset($item['image-position']) && $item['image-position'] != '' ? $item['image-position'] : 'center')
                . ";";
            if( isset($block['image-format']) && $block['image-format'] == 'padded' ) {
                $content .= "background-size:contain;background-repeat:no-repeat;";
            } else {
                $content .= "background-size:cover;";
            }
            // Add image set
            $content .= "background-image: -webkit-image-set("
                . "url({$webp['url']}),"
                . "url({$image['url']}) "
                . ");"; 
            $content .= "'>";
            $content .= '</div>';

            $content .= "<div class='info'>";
            if( isset($block['titles']) && $block['titles'] == 'yes' && isset($item['title']) && $item['title'] != '' ) {
                $content .= "<div class='title'>";
                    $content .= $item['title'];
                $content .= '</div>';
            }
            if( isset($item['content']) && $item['content'] != '' ) {
                $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $item['content']);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.168', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
                }
                $content .= "<div class='text'>" . $rc['content'] . "</div>";
            }
            $content .= '</div>';

            if( $url == 'yes' ) {
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
    if( count($block['items']) > 2 ) {
        $content .= "<div class='buttons'>";

        $content .= "<div class='button-wrap prev'>";
        $content .= "<div class='button' onclick='C.carousel.prev({$carousel_id});'>";
        $content .= '<svg viewBox="0 0 80 80" stroke="#fff" fill="none"><polyline stroke-width="5" stroke-linecap="round" stroke-linejoin="round" points="50,70 20,40 50,10"/></svg>';
        $content .= "</div>";
        $content .= '</div>';

        $content .= "<div class='button-wrap next'>";
        $content .= "<div class='button' onclick='C.carousel.next({$carousel_id});'>";
        $content .= '<svg viewBox="0 0 80 80" stroke="#fff" fill="none"><polyline stroke-width="5" stroke-linecap="round" stroke-linejoin="round" points="30,70 60,40 30,10"/></svg>';
        $content .= "</div>";
        $content .= '</div>';

        $content .= '</div>';
    }


    //
    // Close out block
    //
    $content .= '</div>';
    $content .= '</div>';

    //
    // Setup javascript to initialize and set speed
    //
    $js = '';
    if( isset($block['speed']) && $block['speed'] != 'none' ) {
        if( $block['speed'] == 'turtle' ) {
            $js = "window.addEventListener('load',(e)=>{C.carousel.start(e,{$carousel_id},30000);});";
        } 
        elseif( $block['speed'] == 'xxslow' ) {
            $js = "window.addEventListener('load',(e)=>{C.carousel.start(e,{$carousel_id},25000);});";
        } 
        elseif( $block['speed'] == 'xslow' ) {
            $js = "window.addEventListener('load',(e)=>{C.carousel.start(e,{$carousel_id},20000);});";
        } 
        elseif( $block['speed'] == 'slow' ) {
            $js = "window.addEventListener('load',(e)=>{C.carousel.start(e,{$carousel_id},15000);});";
        }
        elseif( $block['speed'] == 'medium' ) {
            $js = "window.addEventListener('load',(e)=>{C.carousel.start(e,{$carousel_id},10000);});";
        }
        elseif( $block['speed'] == 'fast' ) {
            $js = "window.addEventListener('load',(e)=>{C.carousel.start(e,{$carousel_id},6000);});";
        }
        elseif( $block['speed'] == 'xfast' ) {
            $js = "window.addEventListener('load',(e)=>{C.carousel.start(e,{$carousel_id},3000);});";
        } 
        else {
            $js = "window.addEventListener('load',(e)=>{C.carousel.start(e,{$carousel_id},0);});";
        }
    } else {
        $js = "window.addEventListener('load',(e)=>{C.carousel.start(e,{$carousel_id},0);});";
    }

    return array('stat'=>'ok', 'content'=>$content, 'js'=>$js);
}
?>
