<?php
//
// Description
// -----------
// testimonials
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_testimonials(&$ciniki, $tnid, $request, $block) {

    $content = '';

    if( isset($block['data']) && is_array($block['data']) && count($block['data']) > 0 ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
        $content .= "<div class='block-testimonials"
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . "'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";

        if( isset($block['title']) && $block['title'] != '' ) {
            $content .= "<h2>" . $block['title'] . "</h2>";
        }


        foreach($block['data'] as $testimonial) {
            $content .= "<div class='testimonial-wrap'>";
            $content .= "<div class='testimonial'>";
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $testimonial['content']);   
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( isset($rc['content']) && $rc['content'] != '' ) {
                $content .= $rc['content'];
            }

            $content .= "<div class='author'>";
            if( isset($testimonial['author']) && $testimonial['author'] != '' ) {
                $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $testimonial['author']);   
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                if( isset($rc['content']) && $rc['content'] != '' ) {
                    $content .= $rc['content'];
                }
            }
            
            $content .= '</div>';
            $content .= '</div>';
            $content .= '</div>';
        }

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }


    return array('stat'=>'ok', 'content'=>$content);
}
?>
