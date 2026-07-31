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
function ciniki_wng_generators_flexcards(&$ciniki, $tnid, &$request, $block) {

    $content = '';
   
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'urlProcess');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
    //
    // Skip if nothing
    //
    if( !isset($block['items']) || count($block['items']) < 1 ) {
        return array('stat'=>'ok', 'content'=>'');
    }

    $num_items = count($block['items']);
    //
    // Find the quotients with no remainders. These are used to layout the grid evenly.
    //
    $quotient = '';
    for($i = 2;$i <= 10; $i++) {
        if( ($num_items % $i) == 0 ) {
            $quotient .= " q-{$i}";
        }
    }
    if( $quotient == '' ) {
        $quotient = ' q-prime';
    }

    //
    // Use the sequence number to give each carousel a unique id which 
    // allows several carousels on the same page
    //
    $content .= "<div class='block-flexcards"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";
    $content .= "<div class='items items-{$num_items}{$quotient}'>";

    foreach($block['items'] as $iid => $item) {
        $additional_classes = '';
        if( isset($item['name']) && !isset($item['title']) ) {
            $item['title'] = $item['name'];
        }
        if( isset($item['synopsis']) && !isset($item['content']) ) {
            $item['content'] = $item['synopsis'];
        }
        if( !isset($item['title-position']) && isset($block['title-position']) ) {
            $item['title-position'] = $block['title-position'];
        }
        if( !isset($item['image-format']) && isset($block['image-format']) ) {
            $item['image-format'] = $block['image-format'];
        }
        if( !isset($item['image-ratio']) && isset($block['image-ratio']) ) {
            $item['image-ratio'] = $block['image-ratio'];
        }
        if( isset($item['image-id']) && $item['image-id'] > 0 && is_numeric($item['image-id']) ) {
            //
            // Copy image to cache
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheImageSizes');
            $rc = ciniki_wng_cacheImageSizes($ciniki, $tnid, $request['site'], array( 
                'image_id' => $item['image-id'],
                'version' => (isset($block['image-version']) ? $block['image-version'] : 'original'),
                'maxwidth' => (isset($block['image-size']) ? $block['image-size'] : '2048'),
                'webp' => 'yes',
                ));
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.103', 'msg'=>'', 'err'=>$rc['err']));
            }
            $image = $rc;
        } else {
            $additional_classes .= ' no-image';
            $image = array(
                'url' => 'noimage_240.png',
                );
        }

        $content .= "<div class='item title-"
            . (isset($item['title-position']) && $item['title-position'] != '' ? $item['title-position'] : 'below')
            . $additional_classes
            . "'>";
        $url = 'no';
        if( (isset($item['page']) && $item['page'] > 0) || (isset($item['url']) && $item['url'] != '') ) {
            $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request,
                isset($item['page']) ? $item['page'] : 0,
                isset($item['url']) ? $item['url'] : ''
                );
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.97', 'msg'=>'Unable to process url', 'err'=>$rc['err']));
            }
            $content .= "<a target='" . $rc['target'] . "' href='" . $rc['url'] . "'>";
            $url = 'yes';
        }
        $content .= "<div class='item-wrap'>";

        //
        // Check if title above
        //
        if( isset($item['title-position']) && $item['title-position'] == 'above' 
            && isset($item['title']) && $item['title'] != '' 
            ) {
            $content .= "<div class='title above'><h2>{$item['title']}</h2>";
            if( isset($item['subtitle']) && $item['subtitle'] != '' ) {
                $content .= "<h3>{$item['subtitle']}</h3>";
            }
            $content .= "</div>";
        }

        $content .= "<div class='image-wrap'><div class='image ratio-"
            . (isset($item['image-ratio']) && $item['image-ratio'] ? $item['image-ratio'] : '1-1')
            . "' "
            . "style='background:#fff url(" . $image['url'] . ") "
            . (isset($item['image-position']) && $item['image-position'] != '' ? $item['image-position'] : 'center')
            . ";";
        if( isset($block['image-format']) && $block['image-format'] == 'padded' ) {
            $content .= "background-size:contain;background-repeat:no-repeat;";
        } else {
            $content .= "background-size:cover;";
        }
        // Add image set
        if( isset($image['bg_set']) && $image['bg_set'] != '' ) {
            $content .= "background-image: -webkit-image-set("
                . $image['bg_set']
                . ");"; 
        }
        $content .= "'>";
        if( isset($item['title-position']) && $item['title-position'] != '' 
            && isset($item['title']) && $item['title'] != '' 
            && strncmp($item['title-position'], 'overlay-', 8) == 0 
            ) {
            $content .= "<div class='title overlay'><h2>{$item['title']}</h2>";
            if( isset($item['subtitle']) && $item['subtitle'] != '' ) {
                $content .= "<h3>{$item['subtitle']}</h3>";
            }
            $content .= "</div>";
        }
        $content .= '</div></div>';

        $content .= "<div class='info'>";
        if( (!isset($item['title-position']) || $item['title-position'] == 'below')
            && isset($item['title']) && $item['title'] != '' 
            ) {
            $content .= "<div class='title below'><h2>{$item['title']}</h2>";
            if( isset($item['subtitle']) && $item['subtitle'] != '' ) {
                $content .= "<h3>{$item['subtitle']}</h3>";
            }
            $content .= "</div>";
        } else {
            $content .= "<div class='title below hidden'><h2>{$item['title']}</h2>";
            if( isset($item['subtitle']) && $item['subtitle'] != '' ) {
                $content .= "<h3>{$item['subtitle']}</h3>";
            }
            $content .= "</div>";
        }
        if( isset($item['content']) && $item['content'] != '' ) {
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $item['content']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.90', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= "<div class='text'>" . $rc['content'] . "</div>";
        }
        if( $url == 'yes' && isset($item['link-text']) && $item['link-text'] != '' ) {
            $content .= "<div class='button'>{$item['link-text']}</div>";
        }
        if( isset($item['buttons']) && count($item['buttons']) > 0 ) {
            $content .= "<div class='buttons'>";
            foreach($item['buttons'] as $button) {
                if( isset($button['text']) && $button['text'] != '' ) {
                    $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, 
                        isset($button['page']) ? $button['page'] : 0,
                        $button['url']
                        );
                    if( $rc['stat'] != 'ok' ) {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.240', 'msg'=>'', 'err'=>$rc['err']));
                    }
                    $content .= "<div class='button-wrap"
                        . (isset($button['class']) && $button['class'] != '' ? ' ' . $button['class'] : '')
                        . "'><a class='button' "
                        . (isset($button['target']) && $button['target'] != '' ? "target='{$button['target']}' " : '')
                        . "href='" . $rc['url'] . "'>" . $button['text'] . "</a></div>";
                } 
            }
            $content .= "</div>";
        }
        $content .= '</div>';
        $content .= '</div>';

        if( $url == 'yes' ) {
            $content .= "</a>";
        }

        $content .= '</div>';
    }

    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';

    return array('stat'=>'ok', 'content'=>$content);
}
?>
