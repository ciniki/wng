<?php
//
// Description
// -----------
// Process the cart page
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_cartRequestProcess(&$ciniki, $tnid, &$request) {

    $request['response']['blocks'][] = array(
        'type' => 'content',
        'content' => "<br/></br><center>Cart Page - Not yet implemented</center><br/><br/><br/>",
        );
    
    return array('stat'=>'ok');
}
?>

