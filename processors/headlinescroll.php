<?php
//
// Description
// -----------
// Process the headlines section into blocks.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_processors_headlinescroll(&$ciniki, $tnid, &$request, $section) {
    $blocks = array();

    $headlines = array();

    for($i = 1; $i <= 10; $i++) {
        if( isset($section['settings']["headline-{$i}"]) && $section['settings']["headline-{$i}"] != '' ) {
            $headlines[] = array('headline' => $section['settings']["headline-{$i}"]);
        }
    }

    if( count($headlines) > 0 ) {
        $blocks[] = array(
            'type' => 'headlinescroll',
            'speed' => (isset($section['settings']['speed']) ? $section['settings']['speed'] : 'medium'),
            'data' => $headlines,
            );
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
