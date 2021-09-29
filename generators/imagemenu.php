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

    $dropdown_hmg = 'no';
    $dropdown_full = 'no';
    if( isset($block['dropdown']) && ($block['dropdown'] == 'full' || $block['dropdown'] == 'both') ) {
        $dropdown_full = 'yes';
    }
    if( isset($block['dropdown']) && ($block['dropdown'] == 'hamburger' || $block['dropdown'] == 'both') ) {
        $dropdown_hmg = 'yes';
    }

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
            'maxheight' => 500,
            'quality' => 90,
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.108', 'msg'=>'', 'err'=>$rc['err']));
        }

        //
        // Make sure the image is in the cache
        //
        $content .= "<div class='image-wrap"
            . (isset($block['image-toggle-em']) && $block['image-toggle-em'] != '' ? ' hideat-' . $block['image-toggle-em'] . '-em': '')
            . "'>";
        if( $request['base_url'] == '' ) {
            $content .= "<a href='/'>";
        } else {
            $content .= "<a href='" . $request['base_url'] . "'>";
        }
        $content .= "<img alt='Home' src='" . $rc['url'] . "' />";
        $content .= "</a>";
        $content .= '</div>';
    }

    // 
    // Build the menu
    //
    if( isset($block['main-menu']) && count($block['main-menu']) > 0 ) {
        $content .= "<div class='main-menu " 
            . ($dropdown_full == 'yes' ? 'dropdown ' : '')
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
            $content .= "<a"
                . (isset($item['target']) && $item['target'] != '' ? " target={$item['target']}" : '')
                . " href='" . $item['url'] . "'>";
            if( isset($item['image-id']) && $item['image-id'] > 0 ) {
                //
                // Copy image to cache
                //
                ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheImageAdd');
                $rc = ciniki_wng_cacheImageAdd($ciniki, $tnid, $request['site'], array( 
                    'image_id' => $item['image-id'],
                    'version' => 'original',
                    'maxheight' => 500,
                    'quality' => 90,
                    ));
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.107', 'msg'=>'', 'err'=>$rc['err']));
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
//        $content .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path d="M4,10h24c1.104,0,2-0.896,2-2s-0.896-2-2-2H4C2.896,6,2,6.896,2,8S2.896,10,4,10z M28,14H4c-1.104,0-2,0.896-2,2  s0.896,2,2,2h24c1.104,0,2-0.896,2-2S29.104,14,28,14z M28,22H4c-1.104,0-2,0.896-2,2s0.896,2,2,2h24c1.104,0,2-0.896,2-2  S29.104,22,28,22z"/></svg>';
        $content .= '<svg viewBox="0 0 110 110">'
            . '<rect class="shadow" rx="10" x="3" y="78" width="104" height="20"></rect>'
            . '<rect class="shadow" rx="10" x="3" y="18" width="104" height="20"></rect>'
            . '<rect class="shadow" rx="10" x="3" y="48" width="104" height="20"></rect>'
            . '<rect class="bar" rx="9" x="5" y="80" width="100" height="16"></rect>'
            . '<rect class="bar" rx="9" x="5" y="20" width="100" height="16"></rect>'
            . '<rect class="bar" rx="9" x="5" y="50" width="100" height="16"></rect>'
            . '</svg>';
        $content .= '</div>';
        $content .= '</div>';

        // Menu
        $content .= "<div class='hamburger-menu " 
            . ($dropdown_hmg == 'yes' ? ' dropdown' : '')
            . 'hideat-' . (isset($block['toggle-em']) && $block['toggle-em'] != '' ? $block['toggle-em'] : '60') . '-em'
            . "'>";
        $content .= "<nav id='block-imagemenu-hamburger-menu' class='hidden'><ul>";
        $num = 1;
        foreach($block['hamburger-menu'] as $item) {
            $class = (isset($item['selected']) && $item['selected'] == 'yes' ? ' selected': '')
                . (isset($item['hidden']) && $item['hidden'] == 'yes' ? ' hidden': '')
                . (isset($item['class']) ? ' ' . $item['class'] : '') 
                . "";
            if( $dropdown_hmg == 'yes' && isset($item['items']) && count($item['items']) > 0 ) {
                $content .= "<li id='hmg-{$num}' class='dropdown {$class}'>";
                $content .= "<a class='item' href='" . $item['url'] . "'>" . $item['title'] . '</a>';
                $content .= "<a class='dropdown' onclick='C.hmgT(\"hmg-{$num}\");'><div class='svg'>";
                $content .= '<svg class="expand" viewBox="0 0 100 100">'
                    . '<rect rx="7" x="5" y="45" width="90" height="15"></rect>'
                    . '<rect rx="7" x="45" y="5" width="15" height="90"></rect>'
                    . '</svg>'
                    . '<svg class="close" viewBox="0 0 100 100">'
                    . '<rect rx="7" x="5" y="45" width="90" height="15"></rect>'
                //    . '<rect rx="7" x="45" y="5" width="15" height="90"></rect>'
                    . '</svg>'
                    . "</div></a>";
                //
                // Add the submenu
                //
                $content .= "<ul>";
                foreach($item['items'] as $subitem) {
                    $content .= "<li class='"
                        . (isset($subitem['selected']) && $subitem['selected'] == 'yes' ? ' selected': '')
                        . (isset($subitem['hidden']) && $subitem['hidden'] == 'yes' ? ' hidden': '')
                        . (isset($subitem['class']) ? ' ' . $subitem['class'] : '') 
                        . "'>";
                    $content .= "<a href='" . $subitem['url'] . "'>" . $subitem['title'] . '</a>';
                    $content .= "</li>";
                }
                $content .= "</ul>";
                $content .= "</li>";
                $num++;
            } else {
                $content .= "<li class='{$class}'>";
                $content .= "<a href='" . $item['url'] . "'>" . $item['title'] . '</a>';
                $content .= "</li>";
            }
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
