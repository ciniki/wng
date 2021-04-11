<?php
//
// Description
// -----------
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_wng_generators_headlinescroll(&$ciniki, $tnid, $request, $block) {

    $content = '';

    if( isset($block['data']) && is_array($block['data']) && count($block['data']) > 0 ) {
        $content .= "<div class='block-headlinescroll"
            . (isset($block['speed']) && $block['speed'] != '' ? ' speed-' . $block['speed'] : '')
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . "'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";
        $num_items = 0;
        foreach($block['data'] as $item) {
            if( $num_items > 0 ) {
                $content .= '&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            $content .= "<div class='item'>" . $item['headline'] . "</div>";
            $num_items++;
        }
        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
