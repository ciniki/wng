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

    //error_log(print_r($_SESSION,true));
    //
    // Load the session variables into the request
    //
    if( isset($_SESSION['tnid']) && $_SESSION['tnid'] == $tnid ) {
        $request['session'] = $_SESSION;
        if( !isset($request['session']['tnid']) ) {
            $request['session']['tnid'] = $tnid;
        }
        if( !isset($request['session']['user']['id']) ) {
            $request['session']['user'] = -2;
        }
        if( !isset($request['session']['change_log_id']) ) {
            $request['session']['change_log_id'] = 'web.' . date('Ymd.His');
        }
    } 
    else {
        $request['session'] = array(
            'tnid' => $tnid,
            'user' => -2,
            'change_log_id' => 'web.' . date('Ymd.His'),
            );
        $_SESSION = array();
    }

    //
    // The following variable must be setup for core_dbAddModuleHistory
    //
    if( !isset($ciniki['session']['user']['id']) ) {
        $ciniki['session']['user']['id'] = -2;
    }
    if( !isset($ciniki['session']['change_log_id']) ) {
        $ciniki['session']['change_log_id'] = $request['session']['change_log_id'];
    }

    return array('stat'=>'ok');
}
?>
