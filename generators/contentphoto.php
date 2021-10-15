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

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');

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
            if( isset($block['image-caption']) && $block['image-caption'] != '' ) {
                $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['image-caption']);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.109', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
                }
                $content .= "<div class='caption'>{$rc['content']}</div>";
            }
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
        // Check for phone number or email address to be displayed
        //
        if( isset($block['phone']) && $block['phone'] != '' ) {
            $content .= "<div class='detail phone'>";
            $content .= "<div class='icon'><svg viewbox='0 0 32 32'>"
                . '<path d="M30.8,23l-3.9-3.9c-0.7-0.7-1.5-1.1-2.4-1.1c-0.9,0-1.7,0.4-2.5,1.1l-2.3,2.3c-0.2-0.1-0.4-0.2-0.6-0.3  c-0.3-0.1-0.5-0.3-0.7-0.4c-2.1-1.3-4.1-3.1-5.9-5.4c-0.9-1.1-1.5-2.1-1.9-3.1c0.6-0.5,1.1-1.1,1.7-1.6c0.2-0.2,0.4-0.4,0.6-0.6  c1.5-1.5,1.5-3.5,0-5l-2-2c-0.2-0.2-0.5-0.5-0.7-0.7C9.8,1.9,9.4,1.5,8.9,1.1C8.2,0.4,7.4,0,6.5,0C5.6,0,4.8,0.4,4.1,1.1l0,0  L1.6,3.5c-0.9,0.9-1.4,2-1.6,3.3c-0.2,2.1,0.4,4,0.9,5.3c1.2,3.1,2.9,6,5.5,9.1c3.1,3.7,6.9,6.7,11.2,8.8c1.6,0.8,3.8,1.7,6.3,1.9  c0.2,0,0.3,0,0.5,0c1.7,0,3-0.6,4.1-1.8c0,0,0,0,0,0c0.4-0.5,0.8-0.9,1.3-1.3c0.3-0.3,0.6-0.6,0.9-0.9c0.7-0.7,1.1-1.6,1.1-2.5  C31.9,24.6,31.5,23.7,30.8,23z M29.4,26.6c-0.3,0.3-0.6,0.6-0.9,0.9c-0.5,0.4-0.9,0.9-1.4,1.4c-0.7,0.8-1.6,1.1-2.7,1.1  c-0.1,0-0.2,0-0.3,0c-2.1-0.1-4.1-1-5.6-1.7c-4.1-2-7.6-4.8-10.6-8.3c-2.4-2.9-4.1-5.7-5.2-8.6C2.1,9.7,1.9,8.3,2,7  c0.1-0.8,0.4-1.5,1-2.1l2.4-2.4c0.4-0.3,0.7-0.5,1.1-0.5c0.5,0,0.8,0.3,1,0.5l0,0C8,2.9,8.5,3.3,8.9,3.7C9.1,4,9.3,4.2,9.6,4.4l2,2  c0.8,0.8,0.8,1.5,0,2.2c-0.2,0.2-0.4,0.4-0.6,0.6c-0.6,0.6-1.2,1.2-1.8,1.7c0,0,0,0,0,0c-0.6,0.6-0.5,1.2-0.4,1.6l0,0.1  c0.5,1.2,1.2,2.4,2.3,3.8l0,0c2,2.4,4.1,4.3,6.4,5.8c0.3,0.2,0.6,0.3,0.9,0.5c0.3,0.1,0.5,0.3,0.7,0.4c0,0,0.1,0,0.1,0.1  c0.2,0.1,0.5,0.2,0.7,0.2c0.6,0,1-0.4,1.1-0.5l2.5-2.5c0.2-0.2,0.6-0.5,1.1-0.5c0.4,0,0.8,0.3,1,0.5l4,4  C30.2,25.1,30.2,25.9,29.4,26.6z"/>'
                . "</svg></div>";
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['phone']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.140', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= "<div class='item'><div class='label'>"
                . (isset($block['phone-label']) && $block['phone-label'] != '' ? $block['phone-label'] : "Phone")
                . "</div>";
            if( preg_match("/([0-9][0-9][0-9][^0-9][0-9][0-9][0-9][^0-9][0-9][0-9][0-9][0-9])/", $rc['content'], $m) ) {
                $content .= "<div class='value'><a href='tel:{$m[1]}'>" . $rc['content'] . "</a></div>";
            } else {
                $content .= "<div class='value'>" . $rc['content'] . "</div>";
            }
            $content .= "</div></div>";
        }
        if( isset($block['email']) && $block['email'] != '' ) {
            $content .= "<div class='detail email'>";
            $content .= "<div class='icon'><svg viewbox='0 0 32 32'>"
                . '<path d="M32,6c0-0.1,0-0.1,0-0.2c0-0.1-0.1-0.1-0.1-0.2c0,0,0-0.1-0.1-0.1c0,0,0,0,0,0c0-0.1-0.1-0.1-0.2-0.1  c0,0-0.1-0.1-0.1-0.1c-0.1,0-0.1,0-0.2,0c-0.1,0-0.1,0-0.2,0c0,0,0,0,0,0H1c0,0,0,0-0.1,0c-0.1,0-0.1,0-0.2,0c-0.1,0-0.1,0-0.2,0.1  c-0.1,0-0.1,0.1-0.2,0.1c-0.1,0-0.1,0.1-0.2,0.1c0,0,0,0,0,0c0,0,0,0.1-0.1,0.1c0,0.1-0.1,0.1-0.1,0.2C0,5.9,0,6,0,6  c0,0,0,0.1,0,0.1v19.7c0,0.6,0.4,1,1,1h30c0.6,0,1-0.4,1-1V6.2C32,6.1,32,6.1,32,6z M16.5,17.3L3.9,7.2h24.4L16.5,17.3z M2,24.8V8.3  l13.9,11.1c0,0,0.1,0.1,0.1,0.1c0,0,0.1,0,0.1,0.1c0.1,0,0.2,0.1,0.4,0.1l0,0c0,0,0,0,0,0c0.1,0,0.3,0,0.4-0.1c0,0,0.1,0,0.1-0.1  c0.1,0,0.1-0.1,0.2-0.1L30,8.3v16.5H2z"/>'
                . "</svg></div>";
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['email']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.141', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= "<div class='item'><div class='label'>"
                . (isset($block['email-label']) && $block['email-label'] != '' ? $block['email-label'] : "Email")
                . "</div><div class='value'>" . $rc['content'] . "</div></div>";
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
                    $buttons .= "<a "
                        . (isset($block["button-{$i}-target"]) ? " target='" . $block["button-{$i}-target"] . "' " : '')
                        . "class='"
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
