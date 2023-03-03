<?php
//
// Description
// -----------
// This section displays a list of buttons 
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_processors_buttons(&$ciniki, $tnid, &$request, $section) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();

    $items = array();
    for($i = 1; $i <= 100; $i++) {
        if( isset($s["link-text-{$i}"]) && $s["link-text-{$i}"] != '' 
            && ((isset($s["link-url-{$i}"]) && $s["link-url-{$i}"] != '') 
                || (isset($s["link-page-{$i}"]) && $s["link-page-{$i}"] > 0)
                )
            ) {
            $items[] = array(
                'text' => $s["link-text-{$i}"],
                'page' => isset($s["link-page-{$i}"]) && $s["link-page-{$i}"] > 0 ? $s["link-page-{$i}"] : 0,
                'url' => $s["link-url-{$i}"],
                );
        }
/*        if( isset($s["button-{$i}-text"]) && $s["button-{$i}-text"] != '' 
            && ((isset($s["button-{$i}-url"]) && $s["button-{$i}-url"] != '') 
                || (isset($s["button-{$i}-page"]) && $s["button-{$i}-page"] > 0)
                )
            ) {
            $items[] = array(
                'text' => $s["button-{$i}-text"],
                'page' => isset($s["button-{$i}-page"]) && $s["button-{$i}-page"] > 0 ? $s["button-{$i}-page"] : 0,
                'url' => $s["button-{$i}-url"],
                );
        } */
    }
    if( count($items) > 0 ) {
        $block = array(
            'type' => 'buttons',
            'class' => 'section-' . ciniki_core_makePermalink($ciniki, $section['label']),
            );
        if( isset($s['title']) && $s['title'] > 0 ) {
            $block['title'] = $s['title'];
        }
        if( isset($s['subtitle']) && $s['subtitle'] > 0 ) {
            $block['subtitle'] = $s['subtitle'];
        }
        $block['list'] = $items;
        $blocks[] = $block;
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
