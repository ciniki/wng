<?php
//
// Description
// -----------
//
// API Arguments
// ---------
// email:           The email address of the user to reset.
//
function ciniki_wng_signupRequestProcess(&$ciniki, $tnid, &$request, $args) {
    
    //
    // Log request
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    $rc = ciniki_core_objectAdd($ciniki, $tnid, 'ciniki.wng.signuprequest', [
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'email' => $args['email'],
        'first' => $args['first'],
        'last' => $args['last'],
        ], 0x04);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.253', 'msg'=>'Unable to add the signup request', 'err'=>$rc['err']));
    }
   
    $now = new DateTime('now', new DateTimezone('UTC'));

    //
    // Check honeybot bots
    //
    if( isset($args['email2']) && $args['email2'] != null && $args['email2'] != '' ) {
        error_log("Signup Blocked: {$args['email2']}");
        return array('stat'=>'honeypot', 'err'=>array('code'=>'ciniki.wng.260', 'msg'=>'Too many attempts'));
    }

    //
    // Check log for email request pending in last 2 minute
    //
    $dt = clone $now;
    $dt->sub(new DateInterval('PT5M'));
    $strsql = "SELECT COUNT(*) AS num "
        . "FROM ciniki_customer_signups "
        . "WHERE email = '" . ciniki_core_dbQuote($ciniki, $args['email']) . "' "
        . "AND date_added > '" . ciniki_core_dbQuote($ciniki, $dt->format('Y-m-d H:i:s')) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
    $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.customers', 'num');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.254', 'msg'=>'Unable to check signup logs', 'err'=>$rc['err']));
    }
    if( isset($rc['num']) && $rc['num'] > 0 ) {
        error_log("Signup Blocked: pending {$args['email']}");
        return array('stat'=>'pending', 'err'=>array('code'=>'ciniki.wng.255', 'msg'=>'Pending request'));
    }

    //
    // Check log for too many requests from IP across all tenants in last 24 hours
    //
    $dt = clone $now;
    $dt->sub(new DateInterval('P1D'));
    $strsql = "SELECT COUNT(*) AS num "
        . "FROM ciniki_wng_signuprequests "
        . "WHERE ip_address = '" . ciniki_core_dbQuote($ciniki, $_SERVER['REMOTE_ADDR']) . "' "
        . "AND date_added > '" . ciniki_core_dbQuote($ciniki, $dt->format('Y-m-d H:i:s')) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
    $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.wng', 'num');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.256', 'msg'=>'Unable to check signup logs', 'err'=>$rc['err']));
    }
    if( isset($rc['num']) && $rc['num'] > 5 ) {
        error_log("Signup Blocked: {$rc['num']} attempts in last 24 hours from ip {$_SERVER['REMOTE_ADDR']}");
        return array('stat'=>'blocked', 'err'=>array('code'=>'ciniki.wng.257', 'msg'=>'Too many attempts'));
    }

    //
    // Check log for too many requests from email across all tenants
    //
    $strsql = "SELECT COUNT(*) AS num "
        . "FROM ciniki_wng_signuprequests "
        . "WHERE email = '" . ciniki_core_dbQuote($ciniki, $args['email']) . "' "
        . "AND date_added > '" . ciniki_core_dbQuote($ciniki, $dt->format('Y-m-d H:i:s')) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbSingleCount');
    $rc = ciniki_core_dbSingleCount($ciniki, $strsql, 'ciniki.customers', 'num');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.258', 'msg'=>'Unable to check signup logs', 'err'=>$rc['err']));
    }
    if( isset($rc['num']) && $rc['num'] > 5 ) {
        error_log("Signup Blocked: {$rc['num']} attempts in last 24 hours from email {$args['email']}");
        return array('stat'=>'blocked', 'err'=>array('code'=>'ciniki.wng.259', 'msg'=>'Too many attempts'));
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'wng', 'signupRequestProcess');
    return ciniki_customers_wng_signupRequestProcess($ciniki, $tnid, $request, $args);
}
?>
