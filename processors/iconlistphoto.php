<?php
//
// Description
// -----------
// This section displays a list with icons as bullets.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_processors_iconlistphoto(&$ciniki, $tnid, &$request, $section) {
    
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();

    if( isset($s['text-1']) && $s['text-1'] != '' ) {
        $block = array(
            'type' => 'contentphoto',
            'class' => 'section-' . ciniki_core_makePermalink($ciniki, $section['label']) . ' iconlistphoto',
            );
        if( isset($s['title']) && $s['title'] != '' ) {
            $block['title'] = $s['title'];
        }
        if( isset($s['subtitle']) && $s['subtitle'] != '' ) {
            $block['subtitle'] = $s['subtitle'];
        }
        if( isset($s['image-id']) && $s['image-id'] > 0 ) {
            $block['image-id'] = $s['image-id'];
        }
        $block['image-position'] = isset($s['image-position']) ? $s['image-position'] : 'top-right';
        $block['list'] = array();
        for($i = 1; $i <= 6; $i++) {
            if( isset($s["text-{$i}"]) && $s["text-{$i}"] != '' ) {
                $item = array(
                    'text' => $s["text-{$i}"],
                    );
                if( isset($s["icon-{$i}"]) && $s["icon-{$i}"] > 0 ) {
                    $item['icon-id'] = $s["icon-{$i}"];
                }
                if( isset($s["link-{$i}-page"]) && $s["link-{$i}-page"] != '' ) {
                    $item['link-page'] = $s["link-{$i}-page"];
                    $item['link-text'] = isset($s["link-{$i}-text"]) ? $s["link-{$i}-text"] : '';
                    $item['link-url'] = isset($s["link-{$i}-url"]) ? $s["link-{$i}-url"] : '';
                }
                $block['list'][] = $item;
            }
        }

        // Buttons
        $block['button-1-page'] = isset($s['button-1-page']) ? $s['button-1-page'] : '';
        $block['button-1-text'] = isset($s['button-1-text']) ? $s['button-1-text'] : '';
        $block['button-1-url'] = isset($s['button-1-url']) ? $s['button-1-url'] : '';
        $block['button-2-page'] = isset($s['button-2-page']) ? $s['button-2-page'] : '';
        $block['button-2-text'] = isset($s['button-2-text']) ? $s['button-2-text'] : '';
        $block['button-2-url'] = isset($s['button-2-url']) ? $s['button-2-url'] : '';

        $blocks[] = $block;
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
