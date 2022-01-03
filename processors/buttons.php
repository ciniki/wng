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
    for($i = 1; $i < 15; $i++) {
        if( isset($s["button-{$i}-text"]) && $s["button-{$i}-text"] != '' 
            && ((isset($s["button-{$i}-url"]) && $s["button-{$i}-url"] != '') 
                || (isset($s["button-{$i}-page"]) && $s["button-{$i}-page"] > 0)
                )
            ) {
            $items[] = array(
                'text' => $s["button-{$i}-text"],
                'page' => isset($s["button-{$i}-page"]) && $s["button-{$i}-page"] > 0 ? $s["button-{$i}-page"] : 0,
                'url' => $s["button-{$i}-url"],
                );
        }
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
