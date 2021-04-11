<?php
//
// Description
// -----------
// Process the search page
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_searchRequestProcess(&$ciniki, $tnid, &$request) {

    $request['response']['blocks'][] = array(
        'type' => 'content',
        'content' => "<br/></br><center>Search Page - Not yet implemented</center><br/><br/><br/>",
        );
    
    return array('stat'=>'ok');
}
?>

