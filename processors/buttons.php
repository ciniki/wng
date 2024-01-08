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
                'url' => isset($s["link-url-{$i}"]) ? $s["link-url-{$i}"] : '',
                );
        }
    }
    if( count($items) > 0 ) {
        $blocks[] = array(
            'type' => 'buttons',
            'title' => isset($s['title']) ? $s['title'] : '',
            'level' => $section['sequence'] == 1 ? 1 : 2,
            'subtitle' => isset($s['subtitle']) ? $s['subtitle'] : '',
            'align' => isset($s['align']) ? $s['align'] : '',
            'class' => 'section-' . ciniki_core_makePermalink($ciniki, $section['label']),
            'items' => $items,
            );
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
