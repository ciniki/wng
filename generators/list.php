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
function ciniki_wng_generators_list(&$ciniki, $tnid, $request, $block) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'urlProcess');
    $content = '';

    if( (isset($block['items']) && is_array($block['items']) && count($block['items']) > 0) ) {
        $content .= "<div class='block-list"
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
        $content .= "<ol "
            . (isset($block['list-type']) && $block['list-type'] != '' ? " type='{$block['list-type']}'" : '')
            . (isset($block['list-start']) && $block['list-start'] != '' ? " start='{$block['list-start']}'" : '')
            . "class='list'>";
        foreach($block['items'] as $item) {
            if( $item['content'] != '' ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
                $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $item['content']);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $content .= "<li class='listitem'>";
                $content .= $rc['content'];
                $content .= '</li>';
            }
        }
        $content .= '</ol>';

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
