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
function ciniki_wng_generators_dropdown(&$ciniki, $tnid, $request, $block) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'urlProcess');
    $content = '';

    if( (isset($block['list']) && is_array($block['list']) && count($block['list']) > 0) ) {
        $content .= "<div class='block-dropdown"
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . "'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";

        //
        // Check for any options
        //
        $list_content = '';
        $first_text = '';
        foreach($block['list'] as $item) {
            if( isset($item['text']) && $item['text'] != '' ) {
                $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, 
                    isset($item['page']) ? $item['page'] : 0,
                    $item['url']
                    );
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.227', 'msg'=>'', 'err'=>$rc['err']));
                }
                $list_content .= "<div class='item-wrap"
                    . (isset($item['class']) && $item['class'] != '' ? ' ' . $item['class'] : '')
                    . "'><a class='item' "
                    . (isset($item['target']) && $item['target'] != '' ? "target='{$item['target']}' " : '')
                    . "href='" . $rc['url'] . "'>" . $item['text'] . "</a></div>"
                    . "";
                if( $first_text == '' ) {
                    $first_text = $item['text'];
                }
                if( isset($item['selected']) && $item['selected'] == 'yes' ) {
                    $selected_text = $item['text'];
                }
            } 
        }
        if( !isset($selected_text) ) {
            $selected_text = $first_text;
        }

        $content .= "<div class='selected' onclick='C.dropdown.toggle(\"{$block['id']}\");' stroke-width='7'><span class='text'>" . $selected_text . "</span>"
            . "<span class='arrow'><svg viewBox='0 0 100 100'>"
                . "<line class='line' x1='20' y1='30' x2='50' y2='70' stroke='#000' stroke-width='7' stroke-linecap='round'></line>"
                . "<line class='line' x1='50' y1='70' x2='80' y2='30' stroke='#000' stroke-width='7' stroke-linecap='round'></line>"
            . "</svg></span>"
            . "</div>";
        $content .= "<div id='{$block['id']}' class='items hidden'>" . $list_content . "</div>";

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
