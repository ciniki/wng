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
function ciniki_wng_processors_threetextcol(&$ciniki, $tnid, &$request, $section) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();

    $data = array();
    for($i = 1; $i <= 3; $i++) {
        if( isset($s["content-{$i}"]) && $s["content-{$i}"] != '' ) {
            $data[] = array(
                'title' => isset($s["title-{$i}"]) ? $s["title-{$i}"] : '',
                'content' => isset($s["content-{$i}"]) ? $s["content-{$i}"] : '',
                'button-text' => isset($s["btext-{$i}"]) ? $s["btext-{$i}"] : '',
                'button-url' => isset($s["burl-{$i}"]) ? $s["burl-{$i}"] : '',
                );
        }
    }

    if( count($data) > 0 ) {
        $blocks[] = array(
            'title' => isset($s['title']) ? $s['title'] : '',
            'class' => 'section-' . ciniki_core_makePermalink($ciniki, $section['label']),
            'type' => 'textcolumns',
            'num-cols' => count($data),
            'data' => $data,
            );
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
