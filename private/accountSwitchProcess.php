<?php
//
// Description
// -----------
// This function will switch the session to another customer the current customer has access to.
// This is done with parent/child accounds in ciniki.customers.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_wng_accountSwitchProcess(&$ciniki, $tnid, $request, $customer_id) {

    //
    // Make sure the new account exists
    //
    if( !isset($request['session']['customers'][$customer_id]) ) {
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.wng.120', 'msg'=>'Account does not exist'));
    }

    //
    // Switch the session variables
    //
//    $_SESSION['customer']['email'] = $ciniki['session']['login']['email'];
    $request['session']['customer'] = $request['session']['customers'][$customer_id];

    //
    // call each modules session unload
    //
    foreach($ciniki['tenant']['modules'] as $module => $m) {
        list($pkg, $mod) = explode('.', $module);
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'wng', 'accountSessionUnload');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $tnid, $request);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.121', 'msg'=>'Unable to unload account information', 'err'=>$rc['err']));
            }
        }
        if( isset($request['session'][$module]) ) {
            unset($request['session'][$module]);
        }
    }

    //
    // Call each modules session load for the new user
    //
    foreach($ciniki['tenant']['modules'] as $module => $m) {
        list($pkg, $mod) = explode('.', $module);
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'wng', 'accountSessionLoad');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $tnid, $request);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.122', 'msg'=>'Unable to load account information', 'err'=>$rc['err']));
            }
        }
    }

    //
    // Check for a account switch redirect
    //
    if( isset($request['session']['account_chooser_redirect']) && $request['session']['account_chooser_redirect'] != '' ) {
        $redirect = $request['session']['account_chooser_redirect'];
        $request['session']['account_chooser_redirect'] = '';
        if( $redirect == 'back' 
            && isset($request['session']['login_referer']) && $request['session']['login_referer'] != '' ) {
            header('Location: ' . $request['session']['login_referer']);
            $request['session']['login_referer'] = '';
            return array('stat'=>'exit');
        }
        if( $redirect != '' ) {
            header('Location: ' . $request['ssl_domain_base_url'] . $redirect);
            return array('stat'=>'exit');
        }
    } 
    header('Location: ' . ($request['ssl_domain_base_url'] != '' ? $request['ssl_domain_base_url'] : '') . '/account');
    return array('stat'=>'exit');

    return array('stat'=>'ok');
}
?>
