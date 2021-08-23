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
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.102', 'msg'=>'', 'err'=>$rc['err']));
            }

            //
            // Make sure the image is in the cache
            //
            $content .= "<div class='image-wrap'>";
            $content .= "<img alt='" . (isset($block['title']) ? $block['title'] : '') . "' src='" . $rc['url'] . "' />";
            $content .= '</div>';
        }

        $content .= "<div class='content-wrap'>"; 
        $content .= "<div class='title-wrap'>";
        if( isset($block['sequence']) && $block['sequence'] == 1 && isset($block['title']) && $block['title'] != '' ) {
            $content .= "<h1>" . $block['title'] . "</h1>";
        } elseif( isset($block['title']) && $block['title'] != '' ) {
            $content .= "<h2>" . $block['title'] . "</h2>";
        }
        if( isset($block['sequence']) && $block['sequence'] == 1 && isset($block['subtitle']) && $block['subtitle'] != '' ) {
            $content .= "<h2>" . $block['subtitle'] . "</h2>";
        } elseif( isset($block['subtitle']) && $block['subtitle'] != '' ) {
            $content .= "<h3>" . $block['subtitle'] . "</h3>";
        }
        $content .= "</div>";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
        if( isset($block['content']) && $block['content'] != '' ) {
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['content']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.109', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= $rc['content'];
        } 
        if( isset($block['list']) && is_array($block['list']) && count($block['list']) > 0 ) {
            $content .= "<div class='list'>";
            foreach($block['list'] as $item) {
                $content .= "<div class='list-item'>";

                $url = '';
                if( isset($item['link-page']) && $item['link-page'] != '' ) {
                    $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, 
                        isset($item["link-page"]) ? $item["link-page"] : 0,
                        isset($item["link-url"]) ? $item["link-url"] : ''
                        );
                    if( $rc['stat'] != 'ok' ) {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.117', 'msg'=>'Unable to prepare url', 'err'=>$rc['err']));
                    }
                    $url = $rc['url'];
                }
                if( $url != '' ) {
                    $content .= "<a href='" . $rc['url'] . "'>";
                }
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
                if( $url != '' ) {
                    $content .= "<span class='link'>" . $item["link-text"] . "</span>";
                }
                $content .= "</div>";
                $content .= "</div>";
                if( $url != '' ) {
                    $content .= "</a>";
                }
            }
            $content .= "</div>";
        }
        
        //
        // Check for any buttons
        //
        $buttons = '';
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'urlProcess');
        for($i = 1; $i < 10; $i++) {
            if( (!isset($block["button-{$i}-page"]) || $block["button-{$i}-page"] != '')
                && isset($block["button-{$i}-text"]) && $block["button-{$i}-text"] != '' 
                ) {
                $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, 
                    isset($block["button-{$i}-page"]) ? $block["button-{$i}-page"] : 0,
                    isset($block["button-{$i}-url"]) ? $block["button-{$i}-url"] : ''
                    );
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.110', 'msg'=>'', 'err'=>$rc['err']));
                }
                if( isset($rc['url']) && $rc['url'] != '' ) {
                    $buttons .= "<a class='"
                        . (isset($block['button-class']) && $block['button-class'] != '' ? $block['button-class'] : 'button')
                        . "' href='" . $rc['url'] . "'>" . $block["button-{$i}-text"] . "</a>";
                }
            }
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
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.106', 'msg'=>'', 'err'=>$rc['err']));
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
