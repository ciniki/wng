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

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();

    $titles = 'no';
    $items = array();
    for($i = 1; $i <= 100; $i++) {
        if( isset($s["image-{$i}"]) && $s["image-{$i}"] > 0 ) {
            $items[] = array(
                'image-id' => $s["image-{$i}"],
                'title' => isset($s["title-{$i}"]) && $s["title-{$i}"] != '' ? $s["title-{$i}"] : '&nbsp;',
                'page' => isset($s["link-page-{$i}"]) ? $s["link-page-{$i}"] : (isset($s["link-{$i}-page"]) ? $s["link-{$i}-page"] : 0),
                'aria-label' => isset($s["link-text-{$i}"]) ? $s["link-text-{$i}"] : '',
                'url' => isset($s["link-url-{$i}"]) ? $s["link-url-{$i}"] : (isset($s["link-{$i}-url"]) ? $s["link-{$i}-url"] : ''),
                'image-position' => isset($s["image-position-{$i}"]) ? str_replace('-', ' ', $s["image-position-{$i}"]) : 'center center',
                'content' => isset($s["content-{$i}"]) && $s["content-{$i}"] != '' ? $s["content-{$i}"] : '',
                );
            if( isset($s["title-{$i}"]) && $s["title-{$i}"] != '' ) {
                $titles = 'yes';
            }
        }
    }

    $blocks[] = array(
        'type' => 'carousel',
        'class' => 'section-' . ciniki_core_makePermalink($ciniki, $section['label']),
        'titles' => $titles,
        'sequence' => $section['sequence'],
        'speed' => isset($s['speed']) ? $s['speed'] : 'medium',
        'image-format' => isset($s['image-format']) ? $s['image-format'] : 'cropped',
        'items' => $items,
        );

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
