<?php
//
// Description
// -----------
// Process the list of testimonials into a block.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_processors_testimonials(&$ciniki, $tnid, &$request, $section) {

    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();

    $data = array();
    for($i = 1; $i <= 10; $i++) {
        if( isset($s["content-{$i}"]) && $s["content-{$i}"] != '' ) {
            $data[] = array(
                'content' => isset($s["content-{$i}"]) ? $s["content-{$i}"] : '',
                'author' => isset($s["author-{$i}"]) ? $s["author-{$i}"] : '',
                );
        }
    }

    if( count($data) > 0 ) {
        $blocks[] = array(
            'title' => isset($s['title']) ? $s['title'] : '',
            'type' => 'testimonials',
            'data' => $data,
            );
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
