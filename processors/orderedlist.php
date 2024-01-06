<?php
//
// Description
// -----------
// This section displays a ordered list
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_processors_orderedlist(&$ciniki, $tnid, &$request, $section) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();

    //
    // Find previous section if any
    //
    $start = 1;
    if( isset($s['start']) && is_numeric($s['start']) ) {
        $start = $s['start'];
    }
    else {
        foreach($request['page']['sections'] as $prev_section) {
            if( $section['id'] == $prev_section['id'] ) {
                break;  // Stop at current section
            }
            if( isset($prev_section['ref']) 
                && $prev_section['ref'] == 'ciniki.wng.orderedlist' 
                && isset($prev_section['num_items']) 
                ) {
                if( isset($prev_section['settings']['start']) && is_numeric($prev_section['settings']['start']) ) {
                    $start = $prev_section['settings']['start'];
                }
                $start += $prev_section['num_items'];
            }
        }
    }

    $items = array();
    for($i = 1; $i <= 100; $i++) {
        if( (isset($s["title-{$i}"]) && $s["title-{$i}"] != '')
            || (isset($s["content-{$i}"]) && $s["content-{$i}"] != '')
            ) {
            $items[] = array(
                'title' => isset($s["title-{$i}"]) ? $s["title-{$i}"] : '',
                'content' => isset($s["content-{$i}"]) ? $s["content-{$i}"] : '',
                );
        }
    }
    if( count($items) > 0 ) {
        foreach($request['page']['sections'] as $sid => $sec) {
            if( $sec['id'] == $section['id'] ) {
                $request['page']['sections'][$sid]['num_items'] = count($items);
                break;
            }
        }
        $blocks[] = array(
            'type' => 'list',
            'title' => isset($s['title']) ? $s['title'] : '',
            'level' => $section['sequence'] == 1 ? 1 : 2,
            'subtitle' => isset($s['subtitle']) ? $s['subtitle'] : '',
            'content' => isset($s['content']) ? $s['content'] : '',
            'class' => 'section-' . ciniki_core_makePermalink($ciniki, $section['label']),
            'list-start' => $start,
            'list-type' => isset($s['list-type']) && $s['list-type'] != '' ? $s['list-type'] : '1',
            'items' => $items,
            );
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
