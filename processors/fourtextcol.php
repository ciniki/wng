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
function ciniki_wng_processors_fourtextcol(&$ciniki, $tnid, &$request, $section) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();


    $data = array();
    for($i = 1; $i <= 4; $i++) {
        if( isset($s["content-{$i}"]) && $s["content-{$i}"] != '' ) {
            $coldata = array(
                'title' => isset($s["title-{$i}"]) ? $s["title-{$i}"] : '',
                'content' => isset($s["content-{$i}"]) ? $s["content-{$i}"] : '',
                );
            if( isset($s["button-{$i}-page"]) ) {
                $coldata['button-page'] = isset($s["button-{$i}-page"]) ? $s["button-{$i}-page"] : 0;
                $coldata['button-text'] = isset($s["button-{$i}-text"]) ? $s["button-{$i}-text"] : '';
                $coldata['button-url'] = isset($s["button-{$i}-url"]) ? $s["button-{$i}-url"] : '';
            } else {
                $coldata['button-text'] = isset($s["btext-{$i}"]) ? $s["btext-{$i}"] : '';
                $coldata['button-url'] = isset($s["burl-{$i}"]) ? $s["burl-{$i}"] : '';
            }
            $data[] = $coldata;
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
