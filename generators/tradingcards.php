<?php
//
// Description
// -----------
// tradingcards
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_tradingcards(&$ciniki, $tnid, $request, $block) {

    $content = '';
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'urlProcess');

    //
    // Skip if nothing
    //
//    if( !isset($block['items']) || count($block['items']) < 1 ) {
//        return array('stat'=>'ok', 'content'=>'');
//    }

    $content .= "<div class='block-tradingcards"
        . (isset($block['size']) && $block['size'] != '' ? ' size-' . ($block['size'] == '20' ? 'regular' : $block['size']): ' size-regular')
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";

    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }

    $hlevel = 2;
    if( isset($block['level']) && $block['level'] == 3 ) {
        $hlevel = 3;
    }

    $content .= "<div class='items'>";
    foreach($block['items'] as $iid => $item) {

        $content .= "<div "
            . (isset($item['id-permalink']) && $item['id-permalink'] != '' ? "id='{$item['id-permalink']}' " : '')
            . "class='item"
            . (isset($item['class']) && $item['class'] != '' ? " {$item['class']}" : '') 
            . (!isset($item['image-id']) || $item['image-id'] == 0 ? ' no-image' : '') 
            . "'>";
        if( isset($item['url']) ) {
            $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, 0, $item['url']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.143', 'msg'=>'Unable to process button', 'err'=>$rc['err']));
            }
            $content .= "<a href='" . $rc['url'] . "'>";
        }
        $content .= "<div class='item-wrap'>";

        if( isset($item['image-id']) && $item['image-id'] > 0 ) {
            //
            // Copy image to cache
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheImageAdd');
            $rc = ciniki_wng_cacheImageAdd($ciniki, $tnid, $request['site'], array( 
                'image_id' => $item['image-id'],
                'version' => 'original',
                'padding' => (isset($block['padding']) ? $block['padding'] : ''),
                'maxwidth' => (isset($block['maxwidth']) ? $block['maxwidth'] : '1024'),
                ));
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.144', 'msg'=>'', 'err'=>$rc['err']));
            }
    
            $content .= "<div class='image-wrap'><div class='image ratio-"
                . (isset($item['image-ratio']) && $item['image-ratio'] ? $item['image-ratio'] : '1-1')
                . "' style='background:#fff url(" . $rc['url'] . ") "
                . (isset($item['image-position']) && $item['image-position'] != '' ? $item['image-position'] : 'center')
                . ";";
            if( isset($block['image-format']) && $block['image-format'] == 'padded' ) {
                $content .= "background-size:contain;background-repeat:no-repeat;";
            } else {
                $content .= "background-size:cover;";
            }
            $content .= "'>";
            $content .= '</div></div>';
        } 

        $content .= "<div class='details'>";
        if( isset($item['title']) ) {
            $content .= "<div class='title'><h{$hlevel}>" . $item['title'] . "</h{$hlevel}>";
            if( isset($item['subtitle']) && $item['subtitle'] != '' ) {
                $content .= "<h" . ($hlevel+1) . ">{$item['subtitle']}</h" . ($hlevel+1) . ">";
            }
            $content .= "</div>";
        }
        if( isset($item['meta']) && $item['meta'] != '' ) {
            $content .= "<div class='meta'>" . $item['meta'] . "</div>";
        }
        if( isset($item['synopsis']) ) {
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $item['synopsis']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.13', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= "<div class='synopsis'>" . $rc['content'] . "</div>";
        }
        $content .= "</div>";   // Close details

        $buttons = '';
        for($i = 1; $i <= 10; $i++) {
            if( (!isset($item["button-{$i}-page"]) || $item["button-{$i}-page"] != '')
                && isset($item["button-{$i}-text"]) && $item["button-{$i}-text"] != '' 
                ) {
                $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, 
                    isset($item["button-{$i}-page"]) ? $item["button-{$i}-page"] : 0,
                    isset($item["button-{$i}-url"]) ? $item["button-{$i}-url"] : ''
                    );
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.14', 'msg'=>'Unable to process button', 'err'=>$rc['err']));
                }
                if( isset($rc['url']) && $rc['url'] != '' ) {
                    $buttons .= "<a class='"
                        . (isset($item['button-class']) && $item['button-class'] != '' ? $item['button-class'] : 'button')
                        . "' href='" . $rc['url'] . "'>" . $item["button-{$i}-text"] . "</a>";
                }
            }
        }
        if( $buttons != '' ) {
            $content .= "<div class='buttons'>" . $buttons . "</div>";
        }

        $content .= '</div>';
        if( isset($item['url']) ) {
            $content .= "</a>";
        }
        $content .= '</div>';
    }
    $content .= '</div>';

    //
    // Close out block
    //
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';

    return array('stat'=>'ok', 'content'=>$content);
}
?>
