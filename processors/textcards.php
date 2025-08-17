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
function ciniki_wng_processors_textcards(&$ciniki, $tnid, &$request, $section) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();

    $items = array();
    for($i = 1; $i <= 100; $i++) {
        if( (isset($s["title-{$i}"]) && $s["title-{$i}"] != '') 
            || (isset($s["content-{$i}"]) && $s["content-{$i}"] != '')
            ) {
            $items[] = array(
                'title' => isset($s["title-{$i}"]) && $s["title-{$i}"] != '' ? $s["title-{$i}"] : '&nbsp;',
                'subtitle' => isset($s["subtitle-{$i}"]) && $s["subtitle-{$i}"] != '' ? $s["subtitle-{$i}"] : '',
                'content' => isset($s["content-{$i}"]) && $s["content-{$i}"] != '' ? $s["content-{$i}"] : '',
                'page' => isset($s["link-page-{$i}"]) ? $s["link-page-{$i}"] : (isset($s["link-{$i}-page"]) ? $s["link-{$i}-page"] : 0),
                'link-text' => isset($s["link-text-{$i}"]) ? $s["link-text-{$i}"] : (isset($s["link-{$i}-text"]) ? $s["link-{$i}-text"] : ''),
                'url' => isset($s["link-url-{$i}"]) ? $s["link-url-{$i}"] : (isset($s["link-{$i}-url"]) ? $s["link-{$i}-url"] : ''),
                );
        }
    }

    if( isset($s['title']) && $s['title'] != '' ) {
        $blocks[] = array(
            'type' => 'title',
            'title' => $s['title'],
            );
    }

    $blocks[] = array(
        'type' => 'textcards',
        'class' => 'section-' . ciniki_core_makePermalink($ciniki, $section['label']),
        'sequence' => $section['sequence'],
        'items' => $items,
        );

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
