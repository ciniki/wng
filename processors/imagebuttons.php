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
function ciniki_wng_processors_imagebuttons(&$ciniki, $tnid, &$request, $section) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();

    $title_position = isset($s['title-position']) ? $s['title-position'] : '';
    $image_ratio = isset($s['image-ratio']) ? $s['image-ratio'] : '';

    $items = array();
    for($i = 1; $i <= 100; $i++) {
        if( isset($s["image-{$i}"]) && $s["image-{$i}"] > 0 ) {
            $items[] = array(
                'image-id' => $s["image-{$i}"],
                'image-position' => isset($s["image-position-{$i}"]) ? str_replace('-', ' ', $s["image-position-{$i}"]) : 'center center',
                'image-ratio' => isset($s["image-ratio"]) ? $s["image-ratio"] : $image_ratio,
                'title' => isset($s["title-text-{$i}"]) ? $s["title-text-{$i}"] : (isset($s["title-{$i}-text"]) ? $s["title-{$i}-text"] : ''),
                'title-position' => isset($s["title-position-{$i}"]) ? $s["title-position-{$i}"] : $title_position,
                'page' => isset($s["link-page-{$i}"]) ? $s["link-page-{$i}"] : (isset($s["link-{$i}-page"]) ? $s["link-{$i}-page"] : 0),
                'url' => isset($s["link-url-{$i}"]) ? $s["link-url-{$i}"] : (isset($s["link-{$i}-url"]) ? $s["link-{$i}-url"] : ''),
                );
        }
    }

    if( count($items) > 0 ) {
        if( isset($s['title']) && $s['title'] != '' ) {
            $blocks[] = array(
                'type' => 'title',
                'title' => $s['title'],
                );
        }
        $blocks[] = array(
            'type' => 'imagebuttons',
            'class' => 'section-' . ciniki_core_makePermalink($ciniki, $section['label']),
            'sequence' => $section['sequence'],
            'items' => $items,
            );
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
