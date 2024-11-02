<?php
//
// Description
// -----------
// Generate a h1 title at the top of the page
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_title(&$ciniki, $tnid, $request, $block) {

    $content = '';

    if( isset($block['title']) && $block['title'] != '' ) { 
        $content .= "<div class='block-title"
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . "'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";

        if( isset($block['title_sequence']) && $block['title_sequence'] == 3 ) {
            $content .= "<h3>" . $block['title'] . "</h3>";
        } elseif( isset($block['title_sequence']) && $block['title_sequence'] == 2 ) {
            $content .= "<h2>" . $block['title'] . "</h2>";
        } elseif( isset($block['level']) && $block['level'] == 3 ) {
            $content .= "<h3>" . $block['title'] . "</h3>";
        } elseif( isset($block['level']) && $block['level'] == 2 ) {
            $content .= "<h2>" . $block['title'] . "</h2>";
        } else {
            $content .= "<h1>" . $block['title'] . "</h1>";
        }

        if( isset($block['subtitle']) && $block['subtitle'] != '' ) {
            if( isset($block['title_sequence']) && $block['title_sequence'] == 2 ) {
                $content .= "<h3>" . $block['subtitle'] . "</h3>";
            } elseif( isset($block['level']) && $block['level'] == 2 ) {
                $content .= "<h3>" . $block['subtitle'] . "</h3>";
            } else {
                $content .= "<h2>" . $block['subtitle'] . "</h2>";
            }
        }

        if( isset($block['subsubtitle']) && $block['subsubtitle'] != '' ) {
            if( isset($block['title_sequence']) && $block['title_sequence'] == 2 ) {
                $content .= "<h4>" . $block['subsubtitle'] . "</h4>";
            } elseif( isset($block['level']) && $block['level'] == 2 ) {
                $content .= "<h4>" . $block['subsubtitle'] . "</h4>";
            } else {
                $content .= "<h3>" . $block['subsubtitle'] . "</h3>";
            }
        }

        if( isset($block['meta']) && $block['meta'] != '' ) {
            $content .= "<div class='meta'>" . $block['meta'] . "</div>";
        }

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
