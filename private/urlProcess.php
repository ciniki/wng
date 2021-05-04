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
    $display_url = preg_replace("/^\s*https?:\/\//", '', $url);
    $display_url = preg_replace("/\/\s*$/", '', $display_url);
    if( $page_id > 0 && isset($request['site']['pages'][$page_id]['path']) ) {
        $url = $request['base_url'] . $request['site']['pages'][$page_id]['path'];
        $display_url = $request['site']['pages'][$page_id]['title'];
    }
    elseif( isset($url[0]) && $url[0] == '/' ) {
        $url = $request['base_url'] . $url;
    } 
    //
    // Check if external link
    // FIXME: Add check to see if domain owned by tenant or not
    //
    elseif( preg_match("/^\s*http/", $url) ) {
        $target = '_blank';
    }
    elseif( $url != '' && !preg_match('/^\s*http.*[^\.]\.[^\.]/i', $url) ) {
        $target = '_blank';
        $display_url = $url;
        $url = "http://" . $url;
    } 


    //
    // Check if the url is a email address, without the mailto
    //
    if( $url != '' && preg_match('/^\s*[^ ]+\@[^ ]+\.[^ ]+/i', $url) && !preg_match('/\s*mailto/i', $url) ) {
        $display_url = $url;
        $url = "mailto: " . $url;
    } 

    return array('stat'=>'ok', 'url'=>$url, 'target'=>$target, 'display_url'=>$display_url);
}
?>
