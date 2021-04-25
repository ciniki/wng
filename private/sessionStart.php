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
function ciniki_wng_sessionStart(&$ciniki, $tnid, &$request) {

    session_start();

    //
    // Load the session variables into the request
    //
    if( isset($_SESSION['tnid']) && $_SESSION['tnid'] == $tnid ) {
        $request['session'] = $_SESSION;
    } 
    else {
        $request['session'] = array(
            'tnid' => $tnid,
            'user' => -2,
            'change_log_id' => 'web.' . date('Ymd.His'),
            );
        $_SESSION = array();
    }

    return array('stat'=>'ok');
}
?>
