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
function ciniki_wng_processors_flexcards(&$ciniki, $tnid, &$request, $section) {

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
                'image-ratio' => isset($s["image-ratio-{$i}"]) ? $s["image-ratio-{$i}"] : $image_ratio,
                'title' => isset($s["title-{$i}"]) && $s["title-{$i}"] != '' ? $s["title-{$i}"] : '&nbsp;',
                'title-position' => isset($s["title-position-{$i}"]) ? $s["title-position-{$i}"] : $title_position,
                'page' => isset($s["link-page-{$i}"]) ? $s["link-page-{$i}"] : (isset($s["link-{$i}-page"]) ? $s["link-{$i}-page"] : 0),
                'link-text' => isset($s["link-text-{$i}"]) ? $s["link-text-{$i}"] : (isset($s["link-{$i}-text"]) ? $s["link-{$i}-text"] : ''),
                'url' => isset($s["link-url-{$i}"]) ? $s["link-url-{$i}"] : (isset($s["link-{$i}-url"]) ? $s["link-{$i}-url"] : ''),
                'content' => isset($s["content-{$i}"]) && $s["content-{$i}"] != '' ? $s["content-{$i}"] : '',
                );
        }
    }

    $blocks[] = array(
        'type' => 'flexcards',
        'class' => 'section-' . ciniki_core_makePermalink($ciniki, $section['label']),
        'sequence' => $section['sequence'],
        'items' => $items,
        );

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
