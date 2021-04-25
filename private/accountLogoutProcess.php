<?php
//
// Description
// -----------
// This function will destroy the session and log the customer out.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_wng_accountLogoutProcess(&$ciniki, $tnid, &$request, $timeout) {

    //
    // Clear all the session information
    //
    $request['session'] = array();
    $_SESSION = array();

    //
    // Redirect them back to the home page
    //
    header('Location: ' . ($request['ssl_domain_base_url'] != '' ? $request['ssl_domain_base_url'] : '/'));

    //
    // NOTE: old code from web module could genereate a timeout page, 
    //       if required, add back in from web/private/generatePageAccountLogout
    //

    return array('stat'=>'exit');
}
?>
