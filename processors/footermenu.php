<?php
//
// Description
// -----------
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_processors_footermenu(&$ciniki, $tnid, &$request, $section) {

    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();
  
    $mainmenu = array();
    if( isset($request['site']['footermenu']) ) {
        $page_num = 0;
        foreach($request['site']['footermenu'] as $page_id) {
            if( isset($request['site']['pages'][$page_id]) ) {
                $page = $request['site']['pages'][$page_id];
                $item = array(
                    'title' => $page['title'],
                    'selected' => 'no',
                    'url' => $request['base_url'] . $page['path'],
                    );
                if( $page_id == $request['site']['homepage_id'] 
                    && (!isset($request['uri_split'][0]) || $request['uri_split'][0] == '')
                    ) {
                    $item['selected'] = 'yes';
                }
                elseif( isset($request['uri_split'][0]) && $request['uri_split'][0] == $page['permalink'] ) {
                    $item['selected'] = 'yes';
                }
                if( $page_id == $request['site']['homepage_id'] && isset($s['hide-home']) && $s['hide-home'] == 'yes' ) {
                    $item['hidden'] = 'yes';
                }
                $mainmenu[] = $item;
                $page_num++;
            }
        }
    }

    $block = array(
        'type' => 'imagemenu',
        'class' => 'footermenu',
        'main-menu' =>  $mainmenu,
        'toggle-em' => isset($s['toggle-em']) ? $s['toggle-em'] : '',
        );

    $blocks[] = $block;

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
