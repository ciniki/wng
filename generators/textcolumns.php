<?php
//
// Description
// -----------
// 1 to 4 text columns
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_textcolumns(&$ciniki, $tnid, $request, $block) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'urlProcess');

    $content = '';

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
       
    if( isset($block['data']) && is_array($block['data']) ) {
        $content .= "<div class='block-textcolumns columns-" . count($block['data'])
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . "'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";
    
        $chlvl = 2;
        if( isset($block['title']) && $block['title'] != '' ) {
            $content .= "<h2>" . $block['title'] . "</h2>";
            $chlvl = 3;
        }

        foreach($block['data'] as $col) {
            $content .= "<div class='column'>";
            $content .= "<div class='column-wrap'>";
            if( isset($col['title']) && $col['title'] != '' ) {
                $content .= "<h{$chlvl}>" . $col['title'] . "</h{$chlvl}>";
            }
            if( isset($col['content']) && $col['content'] != '' ) {
                $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $col['content']);   
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                if( isset($rc['content']) && $rc['content'] != '' ) {
                    $content .= $rc['content'];
                }
            }
            if( (!isset($col["button-page"]) || $col["button-page"] != '')
                && isset($col["button-text"]) && $col["button-text"] != '' 
                ) {
                $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, 
                    isset($col["button-page"]) ? $col["button-page"] : 0,
                    isset($col["button-url"]) ? $col["button-url"] : ''
                    );
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.110', 'msg'=>'', 'err'=>$rc['err']));
                }
                if( isset($rc['url']) && $rc['url'] != '' ) {
                    $content .= "<a "
                        . (isset($col["button-target"]) ? " target='" . $col["button-target"] . "' " : '')
                        . "class='"
                        . (isset($col['button-class']) && $col['button-class'] != '' ? $col['button-class'] : 'button')
                        . "' href='" . $rc['url'] . "'>" . $col["button-text"] . "</a>";
                }
            }
            elseif( isset($col['button-text']) && $col['button-text'] != '' 
                && isset($col['button-url']) && $col['button-url'] != '' 
                ) {
                $content .= "<a class='button' href='" . $col['button-url'] . "'>" . $col['button-text'] . "</a>";
            }
            $content .= "</div>";
            $content .= "</div>";
        }

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }


    return array('stat'=>'ok', 'content'=>$content);
}
?>
