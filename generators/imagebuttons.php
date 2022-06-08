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
function ciniki_wng_generators_imagebuttons(&$ciniki, $tnid, &$request, $block) {

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
    $content .= "<div class='block-imagebuttons"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";
    $content .= "<div class='items items-{$num_items}{$quotient}'>";

    foreach($block['items'] as $iid => $item) {
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
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.103', 'msg'=>'', 'err'=>$rc['err']));
            }
            $image = $rc;
        } else {
            $image = array(
                'url' => 'noimage_240.png',
                );
        }

        $content .= "<div class='item title-"
            . (isset($item['title-position']) && $item['title-position'] != '' ? $item['title-position'] : 'below')
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
            $content .= "<a target='" . $rc['target'] . "' href='" . $rc['url'] . "' />";
            $url = 'yes';
        }
        $content .= "<div class='item-wrap'>";

        //
        // Check if title above
        //
        if( isset($item['title-position']) && $item['title-position'] == 'above' 
            && isset($item['title']) && $item['title'] != '' 
            ) {
            $content .= "<div class='title above'><h2>{$item['title']}</h2></div>";
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
        $content .= "'>";
        if( isset($item['title-position']) && $item['title-position'] != '' 
            && isset($item['title']) && $item['title'] != '' 
            && strncmp($item['title-position'], 'overlay-', 8) == 0 
            ) {
            $content .= "<div class='title overlay'><h2>{$item['title']}</h2></div>";
        }
        $content .= '</div></div>';

        $content .= "<div class='info'>";
        if( (!isset($item['title-position']) || $item['title-position'] == 'below')
            && isset($item['title']) && $item['title'] != '' 
            ) {
            $content .= "<div class='title below'><h2>{$item['title']}</h2></div>";
        } else {
            $content .= "<div class='title below hidden'><h2>{$item['title']}</h2></div>";
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
