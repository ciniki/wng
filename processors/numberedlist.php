<?php
//
// Description
// -----------
// This section displays a numbered list
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_processors_numberedlist(&$ciniki, $tnid, &$request, $section) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();

    //
    // Find previous section if any
    //
    $start = 1;
    if( !isset($s['start']) || $s['start'] == '' ) {
        foreach($request['page']['sections'] as $sid => $prev_section) {
            if( $section['id'] == $sid ) {
                break;
            }
            if( isset($prev_section['ref']) 
                && $prev_section['ref'] == 'ciniki.wng.numberedlist' 
                && isset($prev_section['num_items']) 
                ) {
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
        $request['page']['sections'][$section['id']]['num_items'] = count($items);
        $blocks[] = array(
            'type' => 'list',
            'title' => isset($s['title']) ? $s['title'] : '',
            'level' => $section['sequence'] == 1 ? 1 : 2,
            'subtitle' => isset($s['subtitle']) ? $s['subtitle'] : '',
            'content' => isset($s['content']) ? $s['content'] : '',
            'class' => 'section-' . ciniki_core_makePermalink($ciniki, $section['label']),
            'list-start' => $start,
            'list-type' => '1',
            'items' => $items,
            );
            error_log(print_r($blocks,true));
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
