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
function ciniki_wng_sessionSave(&$ciniki, $tnid, $request) {
  
    //error_log('save session');

    $_SESSION = isset($request['session']) ? $request['session'] : array();
    
    //error_log(print_r($_SESSION,true));

    return array('stat'=>'ok');

}
?>
