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
    if( !isset($block['items']) || count($block['items']) < 1 ) {
        return array('stat'=>'ok', 'content'=>'');
    }

    $content .= "<div class='block-tradingcards"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";

    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }

    $content .= "<div class='items'>";
    foreach($block['items'] as $iid => $item) {

        $content .= "<div class='item'>";
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

            $content .= "<div class='image' style='background:url(" . $rc['url'] . ") "
                . (isset($item['image-position']) && $item['image-position'] != '' ? $item['image-position'] : 'center')
                . "; background-size:cover;'>";
            $content .= '</div>';
        } 

        $content .= "<div class='details'>";
        if( isset($item['title']) ) {
            $content .= "<div class='title'>" . $item['title'] . "</div>";
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

        $content .= "<div class='buttons'>";
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
                    $content .= "<a class='"
                        . (isset($item['button-class']) && $item['button-class'] != '' ? $item['button-class'] : 'button')
                        . "' href='" . $rc['url'] . "'>" . $item["button-{$i}-text"] . "</a>";
                }
            }
        }
        $content .= "</div>";

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
