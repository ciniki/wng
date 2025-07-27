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
function ciniki_wng_generators_breadcrumbs(&$ciniki, $tnid, $request, $block) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'urlProcess');
    $js = '';
    $content = '';

    error_log(print_r($block,true));
    if( (isset($block['items']) && is_array($block['items']) && count($block['items']) > 0) ) {
        $content .= "<div class='block-breadcrumbs"
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . (isset($block['align']) && $block['align'] != '' ? ' align' . $block['align'] : '')
            . "'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";
        $content .= "<div class='breadcrumbs'>";

        //
        // Add the 
        //
        $breadcrumbs_content = '';
        $separator = "<span class='separator'>" . (isset($block['separator']) ? $block['separator'] : '>') . '</span>';
        foreach($block['items'] as $item) {
            $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, 
                isset($item['page_id']) ? $item['page_id'] : 0,
                $item['url']
                );
            $url = $rc['url'];
            $breadcrumbs_content .= ($breadcrumbs_content != '' ? $separator : '')
                . "<span class='breadcrumb'><a href='{$url}'>{$item['title']}</a></span>";
        }

        $content .= $breadcrumbs_content;
        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
