<?php
//
// Description
// -----------
// Process the Webhook request
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_webhookRequestProcess(&$ciniki, $tnid, &$request) {

    $settings = isset($request['site']['settings']) ? $request['site']['settings'] : array();

    //
    // Check if maintanence mode
    //
    if( isset($ciniki['config']['ciniki.core']['maintenance']) && $ciniki['config']['ciniki.core']['maintenance'] == 'on' ) {
        if( isset($ciniki['config']['ciniki.core']['maintenance.message']) && $ciniki['config']['ciniki.core']['maintenance.message'] != '' ) {
            $msg = $ciniki['config']['ciniki.core']['maintenance.message'];
        } else {
            $msg = "We are currently doing maintenance on the system and will be back soon.";
        }

        return array('stat'=>'503', 'err'=>array('code'=>'maintenance', 'msg'=>$msg));
    }

    //
    // Check if should be forced to SSL
    //
    if( isset($request['site']['settings']['site-ssl-force-webhook']) 
        && $request['site']['settings']['site-ssl-force-webhook'] == 'yes' 
        ) {
        if( isset($request['site']['settings']['site-ssl-active'])
            && $request['site']['settings']['site-ssl-active'] == 'yes'
            && (!isset($_SERVER['HTTP_CLUSTER_HTTPS']) || $_SERVER['HTTP_CLUSTER_HTTPS'] != 'on')
            && (!isset($_SERVER['HTTP_X_FORWARDED_PROTO']) || $_SERVER['HTTP_X_FORWARDED_PROTO'] != 'https')
            && (!isset($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] != '443' ) 
            ) {
            header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            return array('stat'=>'exit');
        }
    }

    //
    // Make sure enough args passed
    //
    if( isset($request['uri_split'][($request['cur_uri_pos']+2)]) ) {
        $pkg = $request['uri_split'][($request['cur_uri_pos'])];
        $mod = $request['uri_split'][($request['cur_uri_pos']+1)];
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'wng', 'webhook');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $request['cur_uri_pos']+=2;
            return $fn($ciniki, $tnid, $request);
        }
    }

    return array('stat'=>'404', 'err'=>array('code'=>'ciniki.wng.244', 'msg'=>'Webhook Endpoint not found'));
}
?>
