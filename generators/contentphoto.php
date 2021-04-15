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

    if( (isset($block['content']) && $block['content'] != '') 
        || (isset($block['list']) && is_array($block['list']) && count($block['list']) > 0)  
        ) {
        $content .= "<div class='block-contentphoto"
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . (isset($block['image-position']) && $block['image-position'] != '' ? ' image-' . $block['image-position'] : ' image-top-right')
            . (!isset($block['image-id']) || $block['image-id'] == 0 ? ' no-image' : '')
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
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
        if( isset($block['content']) && $block['content'] != '' ) {
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['content']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.88', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= $rc['content'];
        } 
        if( isset($block['list']) && is_array($block['list']) && count($block['list']) > 0 ) {
            $content .= "<div class='list'>";
            foreach($block['list'] as $item) {
                $content .= "<div class='list-item'>";

                if( isset($item['icon-id']) && $item['icon-id'] > 0 ) {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheImageAdd');
                    $rc = ciniki_wng_cacheImageAdd($ciniki, $tnid, $request['site'], array( 
                        'image_id' => $item['icon-id'],
                        'version' => 'original',
                        ));
                    if( $rc['stat'] != 'ok' ) {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.87', 'msg'=>'', 'err'=>$rc['err']));
                    }
                    if( isset($rc['url']) ) {
                        $content .= "<div class='icon'>";
                        $content .= "<img alt='icon' src='" . $rc['url'] . "' />";
                        $content .= '</div>';
                    }
                }
                $content .= "<div class='text'>";
                $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $item['text']);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.88', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
                }
                $content .= $rc['content'];
                $content .= "</div>";
                $content .= "</div>";
            }
            $content .= "</div>";
        }
        
        //
        // Check for any buttons
        //
        $buttons = '';
        if( isset($block['button-1-text']) && $block['button-1-text'] != '' 
            && isset($block['button-1-url']) && $block['button-1-url'] != '' 
            ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'urlProcess');
            $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, $block['button-1-url']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.89', 'msg'=>'', 'err'=>$rc['err']));
            }
            $buttons .= "<a class='button' href='" . $rc['url'] . "'>" . $block['button-1-text'] . "</a>";
        }
        if( isset($block['button-2-text']) && $block['button-2-text'] != '' 
            && isset($block['button-2-url']) && $block['button-2-url'] != '' 
            ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'urlProcess');
            $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, $block['button-2-url']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.89', 'msg'=>'', 'err'=>$rc['err']));
            }
            $buttons .= "<a class='button' href='" . $rc['url'] . "'>" . $block['button-2-text'] . "</a>";
        }
        if( $buttons != '' ) {
            $content .= "<div class='buttons'>" . $buttons . "</div>";
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
