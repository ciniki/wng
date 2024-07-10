<?php
//
// Description
// -----------
// testimonials
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_testimonials(&$ciniki, $tnid, $request, $block) {

    $content = '';

    if( isset($block['data']) && is_array($block['data']) && count($block['data']) > 0 ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
        $content .= "<div class='block-testimonials"
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . (isset($block['scrolling']) && $block['scrolling'] == 'yes' ? ' scrolling' : '')
            . (isset($block['size']) && $block['size'] != '' ? ' size-' . $block['size'] : '')
            . "'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";

        if( isset($block['title']) && $block['title'] != '' ) {
            $content .= "<h2>" . $block['title'] . "</h2>";
        }

        if( isset($block['scrolling']) && $block['scrolling'] == 'yes' ) {
            $carousel_id = isset($block['sequence']) ? $block['sequence'] : 1;
            $content .= "<div class='items' id='carousel-items-{$carousel_id}'>";
            $class = 'current';
        }

        foreach($block['data'] as $iid => $testimonial) {
            if( isset($block['scrolling']) && $block['scrolling'] == 'yes' ) {
                if( $iid == (count($block['data'])-1) && count($block['data']) > 2 ) {
                    $class = 'prev';
                }
                $content .= "<div id='carousel-items-{$carousel_id}-{$iid}' class='item {$class}'>";
            }
            $content .= "<div class='testimonial-wrap'>";
            $content .= "<div class='testimonial'>";
            $content .= "<div class='text'>";
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $testimonial['content']);   
            if( $rc['stat'] != 'ok' ) {
                return $rc; }
            if( isset($rc['content']) && $rc['content'] != '' ) {
                $content .= $rc['content'];
            }
            $content .= '</div>';

            $content .= "<div class='author'>";
            if( isset($testimonial['author']) && $testimonial['author'] != '' ) {
                $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $testimonial['author']);   
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                if( isset($rc['content']) && $rc['content'] != '' ) {
                    $content .= $rc['content'];
                }
            }
            $content .= '</div>';
            
            $content .= '</div>';
            $content .= '</div>';
            if( isset($block['scrolling']) && $block['scrolling'] == 'yes' ) {
                $content .= '</div>';
                if( $class == 'current' ) {
                    $class = 'next';
                } elseif( $class == 'next' ) {
                    $class = '';
                }
            }
        }
        if( isset($block['scrolling']) && $block['scrolling'] == 'yes' ) {
            $content .= "</div>";

            if( count($block['data']) > 2 ) {
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

        }

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }

    //
    // Setup javascript to initialize and set speed
    //
    if( isset($block['scrolling']) && $block['scrolling'] == 'yes' ) {
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

    return array('stat'=>'ok', 'content'=>$content);
}
?>
