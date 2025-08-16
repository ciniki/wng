<?php
//
// Description
// -----------
// Process the four text columns with titles and possible button text
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_processors_buttoncards(&$ciniki, $tnid, &$request, $section) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = [];
    $s = isset($section['settings']) ? $section['settings'] : array();

    $items = [];
    for($i = 1; $i <= 100; $i++) {
        if( (isset($s["title-{$i}"]) && $s["title-{$i}"] != '') 
            || (isset($s["content-{$i}"]) && $s["content-{$i}"] != '')
            ) {
            $item = [
                'title' => isset($s["title-{$i}"]) && $s["title-{$i}"] != '' ? $s["title-{$i}"] : '&nbsp;',
                'content' => isset($s["content-{$i}"]) && $s["content-{$i}"] != '' ? $s["content-{$i}"] : '',
                'buttons' => [],
                ];
            for($j = 1; $j <= 20 ; $j++) {
                if( isset($s["link-{$j}-page-{$i}"]) 
                    && isset($s["link-{$j}-text-{$i}"]) && $s["link-{$j}-text-{$i}"] != '' 
                    ) {
                    $item['buttons'][] = [
                        'page' => isset($s["link-{$j}-page-{$i}"]) ? $s["link-{$j}-page-{$i}"] : 0,
                        'text' => isset($s["link-{$j}-text-{$i}"]) ? $s["link-{$j}-text-{$i}"] : '',
                        'url' => isset($s["link-{$j}-url-{$i}"]) ? $s["link-{$j}-url-{$i}"] : '',
                    ];
                };
            }
            $items[] = $item;
        }
    }

    if( isset($s['title']) && $s['title'] != '' ) {
        $blocks[] = array(
            'type' => 'title',
            'title' => $s['title'],
            );
    }

    $blocks[] = array(
        'type' => 'buttoncards',
        'class' => 'buttoncards section-' . ciniki_core_makePermalink($ciniki, $section['label']),
        'sequence' => $section['sequence'],
        'items' => $items,
        );

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
