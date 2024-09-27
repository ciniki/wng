<?php
//
// Description
// -----------
// Sponsors have unique requirements so they need their own generator
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_sponsors(&$ciniki, $tnid, $request, $block) {

    $content = '';

    if( isset($block['items']) && count($block['items']) > 0 ) {
        $content .= "<div class='block-sponsors"
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . "'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";

        $content .= "<div class='items'>";
        foreach($block['items'] as $item) {
            $content .= "<div class='item'>";
            if( isset($item['url']) && $item['url'] != '' ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'urlProcess');
                $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, (isset($item['page']) ? $item['page'] : 0), $item['url']);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.171', 'msg'=>'Unable to process button', 'err'=>$rc['err']));
                }
                $content .= "<a target='_blank' href='" . $rc['url'] . "'>";
            }
            $content .= "<div class='item-wrap'>";
            
            if( isset($item['image-id']) && $item['image-id'] > 0 && is_numeric($item['image-id']) ) {
                //
                // Copy image to cache
                //
                ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheImageAdd');
                $rc = ciniki_wng_cacheImageAdd($ciniki, $tnid, $request['site'], array( 
                    'image_id' => $item['image-id'],
                    'version' => 'original',
                    'padding' => (isset($block['padding']) ? $block['padding'] : ''),
                    'maxwidth' => (isset($block['maxwidth']) ? $block['maxwidth'] : '600'),
                    ));
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.169', 'msg'=>'', 'err'=>$rc['err']));
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

        $content .= "</div>";

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }


    return array('stat'=>'ok', 'content'=>$content);
}
?>
