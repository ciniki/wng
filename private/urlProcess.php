<?php
//
// Description
// -----------
// Check the URL to see if external or internal, and add base_url if required.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_urlProcess(&$ciniki, $tnid, &$request, $page_id, $url) {
  
    $target = '';
    if( $page_id > 0 && isset($request['site']['pages'][$page_id]['path']) ) {
        $url = $request['base_url'] . $request['site']['pages'][$page_id]['path'];
    }
    elseif( isset($url[0]) && $url[0] == '/' ) {
        $url = $request['base_url'] . $url;
    }

    return array('stat'=>'ok', 'url'=>$url, 'target'=>$target);
}
?>
