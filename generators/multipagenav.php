<?php
//
// Description
// -----------
// Generate the multiple page navigation for the bottom of a page
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_multipagenav(&$ciniki, $tnid, $request, $block) {

    $content = '';
    if( !is_numeric($block['cur-page']) ) {
        $block['cur-page'] = 1;
    }

    $base_url = '';
    if( isset($block['page-path']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'urlProcess');
        $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, 0, $block['page-path']);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.148', 'msg'=>'Unable to prepare url', 'err'=>$rc['err']));
        }
        $base_url = $rc['url'];
    }

    if( isset($block['total-pages']) && isset($block['cur-page']) ) {
        //
        // Start the block
        //
        $content .= "<div class='block-multipagenav"
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . "'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";

        if( is_numeric($block['cur-page']) && $block['cur-page'] > 1 ) {
            //
            // Reverse lets the multipage nav start at the highest page
            //
            if( isset($block['reverse']) && $block['reverse'] == 'yes' ) {
                $content .= "<span class='first'>"
                    . "<a href='" . $base_url . "?page=1'><span class='text'>First</span></a>"
                    . "</span>";
                $content .= "<span class='prev'>"
                    . "<a href='" . $base_url . ($block['cur-page']>2?"?page=".($block['cur-page']-1):'?page=1') . "'><span class='text'>Prev</span></a>"
                    . "</span>";
            } else {
                $content .= "<span class='first'>"
                    . "<a href='" . $base_url . "'><span class='text'>First</span></a>"
                    . "</span>";
                $content .= "<span class='prev'>"
                    . "<a href='" . $base_url . ($block['cur-page']>2?"?page=".($block['cur-page']-1):'') . "'><span class='text'>Prev</span></a>"
                    . "</span>";
            }
        }
        $start = 1;
        $end = $block['total-pages'];
        if( $block['total-pages'] > 5 ) {
            $start = $block['cur-page'] - 2;
            if( $start < 1 ) { 
                $start = 1;
            }
            $end = $start + 4;
            if( $end > $block['total-pages'] ) {
                $end = $block['total-pages'];
                $start = $end - 4;
            }
        }
        for($i = $start; $i <= $end; $i++) {
            if( isset($block['reverse']) && $block['reverse'] == 'yes' ) {
                $content .= "<span class='" . ($i==$block['cur-page']?'selected':'') . "'><a href='" . $base_url . "?page=$i" . "'>"
                    . "<span class='text'>" . $i . "</span></a></span>";
            } else {
                $content .= "<span class='" . ($i==$block['cur-page']?'selected':'') . "'><a href='" . $base_url . ($i>1?"?page=$i":'') . "'>"
                    . "<span class='text'>" . $i . "</span></a></span>";
            }
        }
        if( $block['cur-page'] < $block['total-pages'] ) {
            $content .= "<span class='next'>"
                . "<a href='" . $base_url . "?page=" . ($block['cur-page']+1) . "'><span class='text'>Next</span></a>"
                . "</span>";
            $content .= "<span class='last'>"
                . "<a href='" . $base_url . "?page=" . $block['total-pages'] . "'><span class='text'>Last</span></a>"
                . "</span>";
        }

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
