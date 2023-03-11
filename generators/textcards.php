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
function ciniki_wng_generators_textcards(&$ciniki, $tnid, &$request, $block) {

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
        if( isset($item['synopsis']) && !isset($item['content']) ) {
            $item['content'] = $item['synopsis'];
        }
        $content .= "<div class='item'>";
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

        $content .= "<div class='title'><h2>{$item['title']}</h2>";
        if( isset($item['subtitle']) && $item['subtitle'] != '' ) {
            $content .= "<h3>{$item['subtitle']}</h3>";
        }
        $content .= "</div>";

        $content .= "<div class='info'>";
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
