<?php
//
// Description
// -----------
// textcards
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
    $content .= "<div class='block-textcards"
        . (isset($block['collapsible']) && $block['collapsible'] == 'yes' ? ' collapsible' : '')
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";
    $content .= "<div class='items items-{$num_items}{$quotient}'>";

    $hlevel = 2;
    if( isset($block['level']) && $block['level'] == 3 ) {
        $hlevel = 3;
    }

    foreach($block['items'] as $iid => $item) {
        if( isset($item['synopsis']) && !isset($item['content']) ) {
            $item['content'] = $item['synopsis'];
        }
        if( isset($block['collapsible']) && $block['collapsible'] == 'yes' && isset($item['id-permalink']) ) {
            $content .= "<div id='{$item['id-permalink']}' "
//                . "onclick='javascript:tctoggle(\"{$item['id-permalink']}\");' "
                . "onclick='javascript:C.tpC(event.srcElement,\"item\",\"collapsed\");' "
                . "class='item"
                . (isset($block['collapsed']) && $block['collapsed'] == 'yes' ? ' collapsed' : '')
                . "'>";
        } else {
            $content .= "<div class='item'>";
        }
        $url = 'no';
        $link_url = '';
        //
        // Check if urls in content
        //
        $processed_content = '';
        if( isset($item['content']) && $item['content'] != '' ) {
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $item['content']);
            $processed_content = $rc['content'];
        }
        if( (isset($item['page']) && $item['page'] > 0) || (isset($item['url']) && $item['url'] != '') ) {
            $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request,
                isset($item['page']) ? $item['page'] : 0,
                isset($item['url']) ? $item['url'] : ''
                );
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.228', 'msg'=>'Unable to process url', 'err'=>$rc['err']));
            }
            if( !str_contains($processed_content, '<a ') ) {
                $content .= "<a target='" . $rc['target'] . "' href='" . $rc['url'] . "' />";
                $url = 'yes';
            } else {
                $link_url = $rc['url'];
            }
        }
        $content .= "<div class='item-wrap'>";
        if( (isset($item['title']) && $item['title'] != '') || (isset($item['subtitle']) && $item['subtitle'] != '') ) {
            $content .= "<div class='title'>";
            if( isset($item['title']) && $item['title'] != '' ) {
                $content .= "<h{$hlevel}>" . $item['title'] . "</h{$hlevel}>";
            }
            if( isset($item['subtitle']) && $item['subtitle'] != '' ) {
                $content .= "<h" . ($hlevel+1) . ">{$item['subtitle']}</h" . ($hlevel+1) . ">";
            }
            $content .= "</div>";
        }

        $content .= "<div class='info'>";
        if( isset($item['content']) && $item['content'] != '' ) {
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $item['content']);
            $content .= "<div class='text'>" . $rc['content'] . "</div>";
        }
        $content .= '</div>';
    
        if( $url == 'yes' && isset($item['link-text']) && $item['link-text'] != '' ) {
            $content .= "<div class='button'>{$item['link-text']}</div>";
        } elseif( isset($link_url) && $link_url != '' ) {
            $content .= "<div class='button-wrap'><a class='button' href='{$link_url}'>{$item['link-text']}</a></div>";
        }
        if( isset($item['buttons']) && count($item['buttons']) > 0 ) {
            $content .= "<div class='buttons'>";
            foreach($item['buttons'] as $button) {
                if( isset($button['text']) && $button['text'] != '' ) {
                    $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, 
                        isset($button['page']) ? $button['page'] : 0,
                        $button['url']
                        );
                    if( $rc['stat'] != 'ok' ) {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.240', 'msg'=>'', 'err'=>$rc['err']));
                    }
                    $content .= "<div class='button-wrap"
                        . (isset($button['class']) && $button['class'] != '' ? ' ' . $button['class'] : '')
                        . "'><a class='button' "
                        . (isset($button['target']) && $button['target'] != '' ? "target='{$button['target']}' " : '')
                        . "href='" . $rc['url'] . "'>" . $button['text'] . "</a></div>";
                } 
            }
            $content .= "</div>";
        }
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

    $js = '';
// Converted to use built in javascript function, dec 26, 2023
/*    if( isset($block['collapsible']) && $block['collapsible'] == 'yes' ) {
        $js = "function tctoggle(i,s){"
            . "var e=C.gE(i);"
            . "C.tC(e,'collapsed');"
            . "if(s!=null){e.scrollIntoView();}"
            . "};";
    } */

    return array('stat'=>'ok', 'content'=>$content, 'js'=>$js);
}
?>
