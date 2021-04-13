<?php
//
// Description
// -----------
// Generate the html for the image and menu
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_imagemenu(&$ciniki, $tnid, $request, $block) {

    $content = '';

        
    $content .= "<div class='block-imagemenu"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";
    
    //
    // Add the image, if it's not set to be the home in the menu
    //
    if( isset($block['image-id']) && $block['image-id'] > 0 ) {
        //
        // Copy image to cache
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheImageAdd');
        $rc = ciniki_wng_cacheImageAdd($ciniki, $tnid, $request['site'], array( 
            'image_id' => $block['image-id'],
            'version' => 'original',
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.87', 'msg'=>'', 'err'=>$rc['err']));
        }

        //
        // Make sure the image is in the cache
        //
        $content .= "<div class='image-wrap"
            . (isset($block['image-toggle-em']) && $block['image-toggle-em'] != '' ? ' hideat-' . $block['image-toggle-em'] . '-em': '')
            . "'>";
        $content .= "<a href='" . $request['base_url'] . "'>";
        $content .= "<img alt='Home' src='" . $rc['url'] . "' />";
        $content .= "</a>";
        $content .= '</div>';
    }

    // 
    // Build the menu
    //
    if( isset($block['main-menu']) && count($block['main-menu']) > 0 ) {
        $content .= "<div class='main-menu " 
            . 'showat-' . (isset($block['toggle-em']) && $block['toggle-em'] != '' ? $block['toggle-em'] : '60') . '-em'
            . "'>";
        $content .= "<nav id='block-imagemenu-main-menu' class=''><ul>";
        foreach($block['main-menu'] as $item) {
            $content .= "<li class='" 
                . (isset($item['selected']) && $item['selected'] == 'yes' ? ' selected': '')
                . (isset($item['hidden']) && $item['hidden'] == 'yes' ? ' hidden': '')
                . (isset($item['image-id']) && $item['image-id'] > 0 ? ' image': '')
                . (isset($item['class']) ? $item['class'] : '') 
                . "'>";
            $content .= "<a href='" . $item['url'] . "'>";
            if( isset($item['image-id']) && $item['image-id'] > 0 ) {
                //
                // Copy image to cache
                //
                ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheImageAdd');
                $rc = ciniki_wng_cacheImageAdd($ciniki, $tnid, $request['site'], array( 
                    'image_id' => $item['image-id'],
                    'version' => 'original',
                    ));
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.87', 'msg'=>'', 'err'=>$rc['err']));
                }

                //
                // Make sure the image is in the cache
                //
                $content .= "<img alt='" . $item['title'] . "' src='" . $rc['url'] . "' />";
            } else {
                $content .= $item['title'];
            }
            $content .= '</a>';
            $content .= "</li>";
        }
        $content .= "</ul></nav>";
        $content .= "</div>";
    }

    //
    // Add the hamburger icon
    //
    if( isset($block['hamburger-menu']) && count($block['hamburger-menu']) > 0 ) {
// FIXME: Add suport for search and cart icons
        $content .= "<div class='icons "
            . 'hideat-' . (isset($block['toggle-em']) && $block['toggle-em'] != '' ? $block['toggle-em'] : '60') . '-em'
            . "'>";
/*        // Search Icon
        $content .= "<div onclick='imagemenu_t();' class='"
            . 'hideat-' . (isset($block['toggle-em']) && $block['toggle-em'] != '' ? $block['toggle-em'] : '60') . '-em'
            . "'>";
        $content .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path d="M4,10h24c1.104,0,2-0.896,2-2s-0.896-2-2-2H4C2.896,6,2,6.896,2,8S2.896,10,4,10z M28,14H4c-1.104,0-2,0.896-2,2  s0.896,2,2,2h24c1.104,0,2-0.896,2-2S29.104,14,28,14z M28,22H4c-1.104,0-2,0.896-2,2s0.896,2,2,2h24c1.104,0,2-0.896,2-2  S29.104,22,28,22z"/></svg>';
        $content .= '</div>';
        // Cart Icon
        $content .= "<div onclick='imagemenu_t();' class='"
            . 'hideat-' . (isset($block['toggle-em']) && $block['toggle-em'] != '' ? $block['toggle-em'] : '60') . '-em'
            . "'>";
        $content .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path d="M4,10h24c1.104,0,2-0.896,2-2s-0.896-2-2-2H4C2.896,6,2,6.896,2,8S2.896,10,4,10z M28,14H4c-1.104,0-2,0.896-2,2  s0.896,2,2,2h24c1.104,0,2-0.896,2-2S29.104,14,28,14z M28,22H4c-1.104,0-2,0.896-2,2s0.896,2,2,2h24c1.104,0,2-0.896,2-2  S29.104,22,28,22z"/></svg>';
        $content .= '</div>'; */
        // Hamburger Icon
        $content .= "<div onclick='C.imagemenu.toggle();' class='hamburger-icon'>";
        $content .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path d="M4,10h24c1.104,0,2-0.896,2-2s-0.896-2-2-2H4C2.896,6,2,6.896,2,8S2.896,10,4,10z M28,14H4c-1.104,0-2,0.896-2,2  s0.896,2,2,2h24c1.104,0,2-0.896,2-2S29.104,14,28,14z M28,22H4c-1.104,0-2,0.896-2,2s0.896,2,2,2h24c1.104,0,2-0.896,2-2  S29.104,22,28,22z"/></svg>';
        $content .= '</div>';
        $content .= '</div>';

        // Menu
        $content .= "<div class='hamburger-menu " 
            . 'hideat-' . (isset($block['toggle-em']) && $block['toggle-em'] != '' ? $block['toggle-em'] : '60') . '-em'
            . "'>";
        $content .= "<nav id='block-imagemenu-hamburger-menu' class='hidden'><ul>";
        foreach($block['hamburger-menu'] as $item) {
            $content .= "<li class='" 
                . (isset($item['selected']) && $item['selected'] == 'yes' ? ' selected': '')
                . (isset($item['hidden']) && $item['hidden'] == 'yes' ? ' hidden': '')
                . (isset($item['class']) ? $item['class'] : '') 
                . "'>";
            $content .= "<a href='" . $item['url'] . "'>" . $item['title'] . '</a>';
            $content .= "</li>";
        }
        $content .= "</ul></nav>";
        $content .= "</div>";
    }


    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';



    return array('stat'=>'ok', 'content'=>$content);
}
?>
