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
function ciniki_wng_generators_buttons(&$ciniki, $tnid, $request, $block) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'urlProcess');
    $content = '';

    if( (isset($block['list']) && is_array($block['list']) && count($block['list']) > 0) ) {
        $content .= "<div class='block-buttons"
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . "'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";

        if( isset($block['title']) && $block['title'] != '' ) {
            if( isset($block['level']) && $block['level'] == 2 ) {
                $content .= "<h2>" . $block['title'] . "</h2>";
            } else {
                $content .= "<h1>" . $block['title'] . "</h1>";
            }
        }
        if( isset($block['subtitle']) && $block['subtitle'] != '' ) {
            if( isset($block['level']) && $block['level'] == 2 ) {
                $content .= "<h3>" . $block['subtitle'] . "</h3>";
            } else {
                $content .= "<h2>" . $block['subtitle'] . "</h2>";
            }
        }

        if( isset($block['content']) && $block['content'] != '' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['content']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $content .= $rc['content'];
        }

        //
        // Check for any buttons
        //
        $content .= "<div class='buttons'>";
        foreach($block['list'] as $item) {
            if( !isset($item['text']) && isset($item['title']) ) {
                $item['text'] = $item['title'];
            }
            if( isset($item['text']) && $item['text'] != '' ) {
                $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, 
                    isset($item['page']) ? $item['page'] : 0,
                    $item['url']
                    );
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.89', 'msg'=>'', 'err'=>$rc['err']));
                }
                $content .= "<div class='button-wrap"
                    . (isset($item['class']) && $item['class'] != '' ? ' ' . $item['class'] : '')
                    . "'><a class='button' "
                    . (isset($item['target']) && $item['target'] != '' ? "target='{$item['target']}' " : '')
                    . "href='" . $rc['url'] . "'>" . $item['text'] . "</a></div>";
            } 
        }
        $content .= '</div>';

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
