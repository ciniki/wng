<?php
//
// Description
// -----------
// Process the carousel images
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_processors_carousel(&$ciniki, $tnid, &$request, $section) {

    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();

    $titles = 'no';
    $items = array();
    for($i = 1; $i <= 15; $i++) {
        if( isset($s["image-{$i}"]) && $s["image-{$i}"] > 0 ) {
            $items[] = array(
                'image-id' => $s["image-{$i}"],
                'title' => isset($s["title-{$i}"]) && $s["title-{$i}"] != '' ? $s["title-{$i}"] : '&nbsp;',
                'url' => isset($s["url-{$i}"]) ? $s["url-{$i}"] : '',
                'image-position' => isset($s["image-position-{$i}"]) ? str_replace('-', ' ', $s["image-position-{$i}"]) : 'center center',
                );
            if( isset($s["title-{$i}"]) && $s["title-{$i}"] != '' ) {
                $titles = 'yes';
            }
        }
    }

    $blocks[] = array(
        'type' => 'carousel',
        'titles' => $titles,
        'speed' => isset($s['speed']) ? $s['speed'] : 'medium',
        'items' => $items,
        );

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
