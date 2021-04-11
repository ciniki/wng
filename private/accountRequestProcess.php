<?php
//
// Description
// -----------
// Process the account page
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_accountRequestProcess(&$ciniki, $tnid, &$request) {

    $request['response']['blocks'][] = array(
        'type' => 'content',
        'content' => "<br/></br><center>Account Page - Not yet implemented</center><br/><br/><br/>",
        );
    
    return array('stat'=>'ok');
}
?>
