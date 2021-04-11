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
function ciniki_wng_urlProcess(&$ciniki, $tnid, &$request, $url) {
   
    $target = '';
    if( isset($url[0]) && $url[0] == '/' ) {
        $url = $request['base_url'] . $url;
    }

    return array('stat'=>'ok', 'url'=>$url, 'target'=>$target);
}
?>
