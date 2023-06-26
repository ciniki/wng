<?php
//
// Description
// -----------
// Generate the HTML for a block that displays the list of files for download.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_filelist(&$ciniki, $tnid, $request, $block) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'urlProcess');
    $content = '';

    if( (isset($block['items']) && is_array($block['items']) && count($block['items']) > 0) ) {
        $content .= "<div class='block-filelist"
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . "'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";

        //
        // Check for a title
        //
        if( isset($block['title']) && $block['title'] != '' ) {
            $content .= "<h2>" . $block['title'] . "</h2>";
        }

        //
        // Check for any buttons
        //
        foreach($block['items'] as $item) {
            
            if( isset($item['url']) && $item['url'] != '' ) {
                $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, 
                    isset($item['page']) ? $item['page'] : 0,
                    $item['url']
                    );
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.146', 'msg'=>'', 'err'=>$rc['err']));
                }
                $content .= "<div class='file'>"
                    . "<a target='_blank' class='" . (isset($block['link-class']) ? $block['link-class'] : 'link') . "' href='" . $rc['url'] . "'>" 
                    . $item['name'] 
                    . "</a>"
                    . "</div>";
            }  
        }

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
