<?php
//
// Description
// -----------
// This section displays a list of breadcrumbs 
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_processors_breadcrumbs(&$ciniki, $tnid, &$request, $section) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();
    $show_depth = isset($s['show-depth']) ? $s['show-depth'] : 3;

    if( count($request['breadcrumbs']) >= $show_depth ) {
        // 
        // Don't show the current page item
        //
        array_pop($request['breadcrumbs']);
        $blocks[] = array(
            'type' => 'breadcrumbs',
            'separator' => isset($s['separator']) ? $s['separator'] : '>',
            'align' => isset($s['align']) ? $s['align'] : 'left',
            'items' => $request['breadcrumbs'],
            );
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
