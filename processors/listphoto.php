<?php
//
// Description
// -----------
// This section displays a list
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_processors_listphoto(&$ciniki, $tnid, &$request, $section) {
    
    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();

    if( isset($s['text-1']) && $s['text-1'] != '' ) {
        $block = array(
            'type' => 'contentphoto',
            'class' => 'listphoto', 
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
                if( isset($s["link-{$i}-page"]) && $s["link-{$i}-page"] != '' ) {
                    $item['link-page'] = $s["link-{$i}-page"];
                    $item['link-text'] = isset($s["link-{$i}-text"]) ? $s["link-{$i}-text"] : '';
                    $item['link-url'] = isset($s["link-{$i}-url"]) ? $s["link-{$i}-url"] : '';
                }
                $block['list'][] = $item;
            }
        }

        $blocks[] = $block;
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
