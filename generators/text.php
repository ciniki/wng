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
function ciniki_wng_generators_text(&$ciniki, $tnid, $request, $block) {

    $content = '';

    //
    // Make sure there is content to edit
    //
    if( isset($block['content']) && $block['content'] != '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
        $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['content']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( $rc['content'] != '' ) {
            $content .= "<div "
                . (isset($block['id']) && $block['id'] != '' ? "id='{$block['id']}' " : '')
                . "class='block-text"
                . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
                . (isset($block['columns']) && $block['columns'] != 'no' ? ' columns-' . $block['columns'] : '')
                . "'>";
            $content .= "<div class='wrap'>";
            $content .= "<div class='content'>";
    
            error_log(print_r($block,true));

            if( isset($block['title_sequence']) && $block['title_sequence'] == 1 && isset($block['title']) && $block['title'] != '' ) {
                $content .= "<h1>" . $block['title'] . "</h1>";
            } elseif( isset($block['sequence']) && $block['sequence'] == 1 && isset($block['title']) && $block['title'] != '' ) {
                $content .= "<h1>" . $block['title'] . "</h1>";
            } elseif( isset($block['title']) && $block['title'] != '' ) {
                $content .= "<h2>" . $block['title'] . "</h2>";
            }
            if( isset($block['title_sequence']) && $block['title_sequence'] == 1 && isset($block['subtitle']) && $block['subtitle'] != '' ) {
                $content .= "<h2>" . $block['subtitle'] . "</h2>";
            } elseif( isset($block['sequence']) && $block['sequence'] == 1 && isset($block['subtitle']) && $block['subtitle'] != '' ) {
                $content .= "<h2>" . $block['subtitle'] . "</h2>";
            } elseif( isset($block['subtitle']) && $block['subtitle'] != '' ) {
                $content .= "<h3>" . $block['subtitle'] . "</h3>";
            }
            if( isset($block['title_sequence']) && $block['title_sequence'] == 1 && isset($block['subsubtitle']) && $block['subsubtitle'] != '' ) {
                $content .= "<h3>" . $block['subsubtitle'] . "</h3>";
            } elseif( isset($block['sequence']) && $block['sequence'] == 1 && isset($block['subsubtitle']) && $block['subsubtitle'] != '' ) {
                $content .= "<h3>" . $block['subsubtitle'] . "</h3>";
            } elseif( isset($block['subsubtitle']) && $block['subsubtitle'] != '' ) {
                $content .= "<h4>" . $block['subsubtitle'] . "</h4>";
            }

            if( isset($block['columns']) && $block['columns'] != 'no' ) {
                $content .= "<div class='columns'>";
            }

            $content .= $rc['content'];

            if( isset($block['columns']) && $block['columns'] != 'no' ) {
                $content .= "</div>";
            }

            $content .= '</div>';
            $content .= '</div>';
            $content .= '</div>';
        }
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
