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
function ciniki_wng_generators_accordion(&$ciniki, $tnid, &$request, $block) {

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
    // Use the sequence number to give each carousel a unique id which 
    // allows several carousels on the same page
    //
    $content .= "<div class='block-accordion"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";
    $content .= "<div class='items'>";

    //
    // generate the items
    //
    foreach($block['items'] as $iid => $item) {
        if( isset($item['synopsis']) && !isset($item['content']) ) {
            $item['content'] = $item['synopsis'];
        }
        $content .= "<div "
            . "class='item"
            . (!isset($block['collapsed']) || $block['collapsed'] == 'yes' ? ' collapsed' : '')  // Default to start collapsed
            . "'>";
        $content .= "<div class='item-wrap' "
            . ">";

        //
        // If onlick is attached to item-wrap then user cannot select text from div.info 
        //
        $content .= "<div class='title-wrap' "
            . "onclick='javascript:C.tpC(event.srcElement,\"item\",\"collapsed\");' "
            . "><div class='title'>" . $item['title'] . "</div><div class='toggle'></div></div>";

        $content .= "<div class='info'>";
        if( isset($item['content']) && $item['content'] != '' ) {
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $item['content']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.229', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= $rc['content'];
        }
        $content .= '</div>';

        //
        // FIXME: Add button support
        //
    
        $content .= '</div>';
        $content .= '</div>';
    }

    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';

    return array('stat'=>'ok', 'content'=>$content);
}
?>
