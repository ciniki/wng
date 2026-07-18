<?php
//
// Description
// -----------
// Generate the flex columns
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_flexcols(&$ciniki, $tnid, &$request, $block) {

    $content = '';
   
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'urlProcess');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
    //
    // Skip if nothing
    //
    if( !isset($block['columns']) || count($block['columns']) < 1 ) {
        return array('stat'=>'ok', 'content'=>'');
    }

    $num_cols = count($block['columns']);
    //
    // Find the quotients with no remainders. These are used to layout the grid evenly.
    //
    $quotient = '';
    for($i = 2;$i <= 10; $i++) {
        if( ($num_cols % $i) == 0 ) {
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
    $content .= "<div class='block-flexcols"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";
    $content .= "<div class='columns columns-{$num_cols}{$quotient}'>";

    foreach($block['columns'] as $cid => $column) {
        $content .= "<div class='column size-"
            . (isset($column['size']) && $column['size'] != '' ? $column['size'] : 'medium')
            . "'>";
        foreach($column['items'] as $iid => $item) {
            if( isset($item['type']) && $item['type'] == 'title' && isset($item['title']) && $item['title'] != '' ) {
                $content .= "<div class='title'><h2>" . $item['title'] . "</h2></div>";
            }
            if( isset($item['type']) && $item['type'] == 'image' && isset($item['image-id']) && $item['image-id'] > 0 && is_numeric($item['image-id']) ) {
                //
                // Check if url for image 
                //
                $link_url != '';
                if( (isset($item['page']) && $link['page'] != '')
                    || (isset($item['url']) && $link['url'] != '')
                    ) {
                    $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request,
                        isset($item['page']) ? $item['page'] : 0,
                        isset($item['url']) ? $item['url'] : ''
                        );
                    if( $rc['stat'] != 'ok' ) {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.97', 'msg'=>'Unable to process url', 'err'=>$rc['err']));
                    }
                    if( isset($rc['url']) ) {
                        $link_url = $rc['url'];
                    }
                }

                //
                // Copy image to cache
                //
                ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheImageSizes');
                $rc = ciniki_wng_cacheImageAdd($ciniki, $tnid, $request['site'], array( 
                    'image_id' => $item['image-id'],
                    'version' => (isset($block['image-version']) ? $block['image-version'] : 'original'),
                    'maxwidth' => (isset($block['image-size']) ? $block['image-size'] : '1024'),
                    ));
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.262', 'msg'=>'', 'err'=>$rc['err']));
                }
                if( isset($rc['url']) ) {
                    if( $link_url != '' ) {
                        $content .= "<div class='image'><a href='{$link_url}'><img src='{$rc['url']}'></a></div>";
                    } else {
                        $content .= "<div class='image'><img src='{$rc['url']}'></div>";
                    }
                }
            }
            if( isset($item['type']) && $item['type'] == 'content' && isset($item['content']) && $item['content'] != '' ) {
                $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $item['content']);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.263', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
                }
                $content .= "<div class='text'>" . $rc['content'] . "</div>";
            }
            if( isset($item['type']) && $item['type'] == 'links' && isset($item['items']) && is_array($item['items']) && count($item['items']) > 0 ) {
                $link_content = '';
                foreach($item['items'] as $link) {
                    if( isset($link['text']) && $link['text'] != '' ) {
                        $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, (isset($link['page']) ? $link['page'] : 0), $link['url']);
                        if( $rc['stat'] != 'ok' ) {
                            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.264', 'msg'=>'', 'err'=>$rc['err']));
                        }
                        $link_content .= "<div class='link-wrap"
                            . "'><a class='link' "
                            . (isset($link['target']) && $link['target'] != '' ? "target='{$link['target']}' " : '')
                            . "href='" . $rc['url'] . "'>" . $link['text'] . "</a></div>";
                    }
                }
                if( $link_content != '' ) {
                    $content .= "<div class='links'>" . $link_content . "</div>";
                }
            }
            if( isset($item['type']) && $item['type'] == 'buttons' && isset($item['items']) && is_array($item['items']) && count($item['items']) > 0 ) {
                $button_content = '';
                foreach($item['items'] as $button) {
                    if( isset($button['text']) && $button['text'] != '' ) {
                        $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, (isset($button['page']) ? $button['page'] : 0), $button['url']);
                        if( $rc['stat'] != 'ok' ) {
                            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.265', 'msg'=>'', 'err'=>$rc['err']));
                        }
                        $button_content .= "<div class='button-wrap"
                            . "'><a class='button' "
                            . (isset($button['target']) && $button['target'] != '' ? "target='{$button['target']}' " : '')
                            . "href='" . $rc['url'] . "'>" . $button['text'] . "</a></div>";
                    } 
                }
                if( $button_content != '' ) {
                    $content .= "<div class='buttons'>" . $button_content . "</div>";
                }
            }
        }
        $content .= "</div>";
    }

    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';

    return array('stat'=>'ok', 'content'=>$content);
}
?>
