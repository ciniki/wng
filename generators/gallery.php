<?php
//
// Description
// -----------
// template
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_gallery(&$ciniki, $tnid, $request, $block) {

    $content = '';

    if( isset($block['items']) && count($block['items']) > 0 ) {
        $content .= "<div class='block-gallery"
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . (isset($block['layout']) && $block['layout'] != '' ? ' layout-' . $block['layout'] : '')
            . "'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";

        if( isset($block['sequence']) && $block['sequence'] == 1 && isset($block['title']) && $block['title'] != '' ) {
            $content .= "<h1>" . $block['title'] . "</h1>";
        } elseif( isset($block['title']) && $block['title'] != '' ) {
            $content .= "<h2>" . $block['title'] . "</h2>";
        }

        $content .= "<div class='items'>";

        if( isset($block['layout']) && $block['layout'] == 'originals' ) {
            foreach($block['items'] as $item) {
                if( isset($item['image-id']) && $item['image-id'] > 0 ) {
                    if( isset($item['url']) ) {
                        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'urlProcess');
                        $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, (isset($item['page']) ? $item['page'] : 0), $item['url']);
                        if( $rc['stat'] != 'ok' ) {
                            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.214', 'msg'=>'Unable to process button', 'err'=>$rc['err']));
                        }
                        $content .= "<a class='item' href='" . $rc['url'] . "'>";
                    } else {
                        $content .= "<div class='item'>";
                    }
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheImageAdd');
                    $rc = ciniki_wng_cacheImageAdd($ciniki, $tnid, $request['site'], array( 
                        'image_id' => $item['image-id'],
                        'version' => 'original',
                        'maxwidth' => (isset($block['maxwidth']) ? $block['maxwidth'] : '1024'),
                        ));
                    if( $rc['stat'] != 'ok' ) {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.210', 'msg'=>'', 'err'=>$rc['err']));
                    }
                    $alt = isset($item['title']) ? $item['title'] : '';
                    $content .= "<img alt='{$alt}' src='{$rc['url']}' />";
                    if( isset($item['url']) ) {
                        $content .= "</a>";
                    } else {
                        $content .= "</div>";
                    }
                }
            } 
            $content .= "<div class='item'></div>";
        }
        else {
            foreach($block['items'] as $item) {
                $content .= "<div class='item'>";
                if( isset($item['url']) ) {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'urlProcess');
                    $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, (isset($item['page']) ? $item['page'] : 0), $item['url']);
                    if( $rc['stat'] != 'ok' ) {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.215', 'msg'=>'Unable to process button', 'err'=>$rc['err']));
                    }
                    $content .= "<a target='{$rc['target']}' href='" . $rc['url'] . "'>";
                }
                $content .= "<div class='item-wrap'>";
                
                if( isset($item['image-id']) && $item['image-id'] > 0 ) {
                    //
                    // Copy image to cache
                    //
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheImageAdd');
                    $rc = ciniki_wng_cacheImageAdd($ciniki, $tnid, $request['site'], array( 
                        'image_id' => $item['image-id'],
                        'version' => (isset($block['padding']) && $block['padding'] != '' ? 'original' : 'thumbnail'),
                        'padding' => (isset($block['padding']) ? $block['padding'] : ''),
                        'maxwidth' => (isset($block['maxwidth']) ? $block['maxwidth'] : '1024'),
                        ));
                    if( $rc['stat'] != 'ok' ) {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.213', 'msg'=>'', 'err'=>$rc['err']));
                    }

    //                $content .= "<div class='image' style='background:url(" . $rc['url'] . ") "
    //                    . (isset($item['image-position']) && $item['image-position'] != '' ? $item['image-position'] : 'center')
    //                    . "; background-size:cover;'>";
                    $alt = isset($item['title']) ? $item['title'] : '';
                    $content .= "<div class='image'><img alt='{$alt}' src='{$rc['url']}' /></div>";
                } 
                $content .= '</div>';
                if( isset($item['url']) ) {
                    $content .= '</a>';
                }
                $content .= '</div>';
            }
        }

        $content .= "</div>";

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }


    return array('stat'=>'ok', 'content'=>$content);
}
?>
